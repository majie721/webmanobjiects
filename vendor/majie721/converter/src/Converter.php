<?php

namespace Majie\Converter;

use JsonException;
use Majie\Converter\Exceptions\ConverterClassException;
use Majie\Converter\Exceptions\PropertyException;
use Majie\Converter\Traits\PropertyArrayAccessTrait;
use Majie\Converter\PropertyParser;

class Converter implements \ArrayAccess
{

    use PropertyArrayAccessTrait;

    public function __construct($data = null){
        $this->beforeFill($data);
        if(is_array($data) || is_object($data)){
            $this->fillAction($data);
        }
        $this->afterFill();
    }



    protected function beforeFill(&$data){

    }

    protected function afterFill(){

    }


    /**
     * @param array|object|null $data
     * @return Converter
     * @throws ConverterClassException
     * @throws JsonException
     * @throws PropertyException
     * @throws \ReflectionException
     */
    public function fillAction(array|object|null $data){
        $parser =  new PropertyParser($this);
        $parser->fillData($data);
        return $this;
    }


    /**
     * @return array
     * @throws Exceptions\PropertyException
     * @throws \ReflectionException
     */
    public static function getPropertiesInfo(): array
    {
        $parser =  new PropertyParser(static::class);
        return $parser->getProxyPropertyData();
    }

    /**
     * 获取属性列表
     * @return string[]
     * @throws PropertyException
     * @throws \ReflectionException
     */
    public static function getProperties():array{
        $data =  self::getPropertiesInfo();
        return array_keys($data);
    }

    /**
     * @return array
     * @throws JsonException
     */
    public function toArray():array{
        return (array)json_decode(json_encode($this, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }


    /**
     * @return string
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }


    /**
     * 将对象数组转为当前对象数组
     *
     * @param $data
     * @return $this
     */
    public static function fromItem($data): self
    {
        return new static($data);
    }

    /**
     * 将多维数组转为当前对象数组
     *
     * @param array $list
     * @return static[]
     */
    public static function fromList(array $list): array
    {
        $data = [];
        foreach ($list as $item) {
            $data[] = new static($item);
        }
        return $data;
    }

}