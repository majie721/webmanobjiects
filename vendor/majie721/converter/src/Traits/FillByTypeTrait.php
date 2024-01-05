<?php

namespace Majie\Converter\Traits;

use Majie\Converter\PropertyInfo;
use Majie\Converter\Types;

trait FillByTypeTrait
{

    /**
     * string类型赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillString(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }

        if (is_string($value) || is_numeric($value)) {
            $this->setPropertyValue($propertyInfo, (string)$value);
            return;
        }
        $this->typeError($propertyInfo->propertyName, $value);
    }

    /**
     * 浮点类型赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillFloat(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }

        if (is_int($value) || is_float($value) || (is_string($value) && preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $value))) {
            $this->setPropertyValue($propertyInfo, (float)$value);
            return;
        }
        $this->typeError($propertyInfo->propertyName, $value);
    }

    /**
     * int 类型赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillInt(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }

        if (is_numeric($value) && preg_match('/^-?\d+$/', (string)$value)) {
            $this->setPropertyValue($propertyInfo, (int)$value);
            return;
        }
        $this->typeError($propertyInfo->propertyName, $value);
    }

    /** bool类型赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillBool(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }

        if (is_bool($value) || in_array($value,[0,1],true)) {
            $this->setPropertyValue($propertyInfo, (bool)$value);
            return;
        }
        $this->typeError($propertyInfo->propertyName, $value);
    }

    /**
     * FALSE 类型赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillFalse(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }

        if (false === $value || $value===0) {
            $this->setPropertyValue($propertyInfo, false);
            return;
        }

        $this->typeError($propertyInfo->propertyName, $value);
    }

    /**
     * True类型赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillTrue(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }

        if (true === $value || $value===1) {
            $this->setPropertyValue($propertyInfo, true);
            return;
        }

        $this->typeError($propertyInfo->propertyName, $value);
    }

    /**
     *  mixed 直接赋值
     * @param \Majie\Converter\PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillMixed(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }

        $this->setPropertyValue($propertyInfo, $value);
    }


    /**
     * null类型赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillNull(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }

        $this->typeError($propertyInfo->propertyName, $value);
    }

    /**
     * 枚举值填充
     * @param \Majie\Converter\PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillEnum(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }
        try {
            $this->setPropertyValue($propertyInfo, $propertyInfo->typeName::from($value));
        } catch (\ValueError $e) {
            $class = get_class($this->instance);
            throw new \ValueError(sprintf("Can not fill value to the property %s::%s;error: %s", $class, $propertyInfo->propertyName, $e->getMessage()));
        }

    }


    /**
     * 自定义类赋值
     * @param \Majie\Converter\PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillCustomClass(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }
        $instance = new $propertyInfo->typeName($value);
        $this->setPropertyValue($propertyInfo, $instance);
    }


    /**
     * 给数组类型的属性赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    public function fillArray(PropertyInfo $propertyInfo, mixed $value): void
    {
        if (null === $value && $propertyInfo->allowsNull) {
            $this->setPropertyValue($propertyInfo, null);
            return;
        }
        if (is_array($value)) {
            if ('' !== $propertyInfo->arrayType && class_exists($propertyInfo->arrayType) && !str_ends_with($propertyInfo->arrayType, ']')) { //todo 多维数组处理后赋值
                if(!in_array($propertyInfo->arrayType, ['int','float','bool','true','false','string','array','object','mixed'],true)){
                    foreach ($value as &$item) {
                        $item = new $propertyInfo->arrayType($item);
                    }
                }
            }
            $this->setPropertyValue($propertyInfo, $value);
            return;
        }
        $this->typeError($propertyInfo->propertyName, $value);
    }





    /**
     * 赋值
     * @param PropertyInfo $propertyInfo
     * @param mixed $value
     * @return void
     */
    private function setPropertyValue(PropertyInfo $propertyInfo, mixed $value): void
    {
        $this->instance->{$propertyInfo->propertyName} = $value;
    }

    private function typeError(string $propertyName, mixed $value)
    {
        $class = get_class($this->instance);
        $type = gettype($value);
        throw new \TypeError(sprintf("Cannot assign %s to property %s::%s's type", $type, $class, $propertyName));
    }
}
