<?php

namespace Majie\Converter;

use Majie\Converter\Exceptions\ConverterClassException;
use Majie\Converter\Exceptions\PropertyException;
use Majie\Converter\Traits\FillByTypeTrait;
use ReflectionProperty;

class PropertyParser
{

    use FillByTypeTrait;

    /** @var string 解析对象的类名 */
    private string $className;

    /** @var object 解析对象的实例 */
    private object $instance;

    /** @var array 解析对象信息 */
    public static array $proxyPropertyPoll;

    /**
     * @throws ConverterClassException
     */
    public function __construct(string|object $class)
    {
        if (is_string($class)) {
            $this->initClassNameWithString($class);
        } else {
            $this->initClassNameWithObject($class);
        }
    }

    /**
     * @throws ConverterClassException
     */
    private function initClassNameWithString(string $class): void
    {
        if (!class_exists($class)) {
            throw new ConverterClassException("class not exists,  $class",$class);
        }
        $this->className = $class;
        $this->instance = new $class;
    }

    private function initClassNameWithObject(object $class): void
    {
        $this->className = get_class($class);
        $this->instance = $class;
    }


    /**
     * @return array<string,PropertyInfo>
     * @throws PropertyException
     * @throws \ReflectionException
     * @noinspection
     */
    public function getProxyPropertyData(): array
    {
        $this->parseProxyPropertyData($this->className);
        return self::$proxyPropertyPoll[$this->className] ?? [];
    }


    /**
     * @param string $className
     * @return void
     * @throws PropertyException
     * @throws \ReflectionException
     */
    private function parseProxyPropertyData(string $className): void
    {
        $proxyProperty = self::$proxyPropertyPoll[$className] ?? null;
        if (null === $proxyProperty) {
            $reflection = new \ReflectionClass($className);
            //获取public的属性
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($properties as $property) {
                if ($this->writable($property)) {
                    $this->verifyProperty($property, $className);
                    $propertyInfo = new PropertyInfo();
                    $propertyInfo->propertyName = $property->getName();
                    $propertyInfo->hasDefaultValue = $property->hasDefaultValue();
                    $propertyInfo->defaultValue = $property->getDefaultValue();
                    $propertyInfo->allowsNull = $property->getType()->allowsNull();
                    $propertyInfo->isBuiltin = $property->getType()->isBuiltin();
                    $propertyInfo->typeName = $property->getType()->getName();
                    $propertyInfo->attributes = $property->getAttributes();
                    $propertyInfo->arrayType = $propertyInfo->typeName === Types::Array ? $this->parseArrayType($property):'';
                    self::$proxyPropertyPoll[$className][$propertyInfo->propertyName] = $propertyInfo;
                }
            }
        }
    }


    /**
     * @param object|array|null $fillingData
     * @return PropertyParser|void
     * @throws PropertyException
     * @throws \JsonException
     * @throws \ReflectionException|\Majie\Converter\Exceptions\ConverterClassException
     */
    public function fillData(object|array|null $fillingData)
    {

        if (null === $fillingData) {
            return $this;
        }

        if (is_object($fillingData)) {
            $fillingData = json_decode(json_encode($fillingData, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        }

        $classPropertyList = $this->getProxyPropertyData();

        foreach ($fillingData as $keyName => $datum) {
            $propertyInfo = $classPropertyList[$keyName] ?? null;
            if ($propertyInfo) {
                try {
                    if ($propertyInfo->isBuiltin) {
                        $this->fillWithBuiltin($propertyInfo, $datum);
                    } else {
                        $this->fillWithCustom($propertyInfo,$datum);
                    }
                }catch (\Throwable $e){
                    throw new ConverterClassException($e->getMessage(),get_class($this->instance),$propertyInfo,$datum,$e);
                }

            }
        }
    }


    /**
     * 标量赋值(包含数组)
     * @param \Majie\Converter\PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    private function fillWithBuiltin(PropertyInfo $propertyInfo, mixed $value): void
    {
        $name = ucfirst($propertyInfo->typeName);
        $funcName = "fill{$name}";
        $this->$funcName($propertyInfo, $value);
    }

    /**
     * 自定义类赋值(对象或者是回退枚举)
     * @param \Majie\Converter\PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     * @throws \ReflectionException
     */
    private function fillWithCustom(PropertyInfo $propertyInfo, mixed $value): void
    {
        $reflectClass = new \ReflectionClass($propertyInfo->typeName);
        if($reflectClass->isEnum()){
            $this->fillEnum($propertyInfo, $value);
        }else{
            $this->fillCustomClass($propertyInfo, $value);
        }
    }


    /** 判断对象的属性是否能够填充
     * @param ReflectionProperty $property
     * @return bool
     */
    private function writable(ReflectionProperty $property): bool
    {
        return $property->isPublic() && !$property->isReadOnly();
    }


    /**
     * 1.仅支持NamedType.(null和联合类型不支持属性覆盖)
     * 2.标量支持的类型验证
     * @param ReflectionProperty $property
     * @param string $className
     * @return void
     * @throws PropertyException
     */
    private function verifyProperty(ReflectionProperty $property, string $className): void
    {
        $propertyName = $property->getName();
        if (null === $property->getType()) {
            $message = sprintf("The type of the attribute is not defined in %s::%s", $className, $propertyName);
            throw new PropertyException($message);
        }

        if (!($property->getType() instanceof \ReflectionNamedType)) {
            $message = sprintf("The property type is not NamedType in %s::%s", $className, $propertyName);
            throw new PropertyException($message);
        }

        if ($property->getType()->isBuiltin()) {
            $type = $property->getType()->getName();
            if (!in_array($type, Types::allowFill())) {
                $message = sprintf("The property type not support %s in %s::%s", $type, $className, $propertyName);
                throw new PropertyException($message);
            }
        }
    }


    /**
     * 通过注释解析类似数组元素的类型(eg:  "** @var ?\Model\Order[] 订单 *")
     * @param \ReflectionProperty $property
     * @return string
     */
    private function parseArrayType(ReflectionProperty $property):string{
        $docComment = $property->getDocComment();
        if($docComment){
            preg_match("/@var\s+\??(.*?)(\[\])?[\|\s\*]/", $docComment, $matches);
        }
        return $matches[1] ?? '';
    }


}