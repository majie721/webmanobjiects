<?php

namespace Majie\Converter;

class PropertyInfo
{
    /** @var string 属性名称 */
    public string $propertyName;

    /** @var boolean 是否有默认值 */
    public bool $hasDefaultValue;

    /** @var mixed 默认值 */
    public mixed $defaultValue;

    /** @var bool 是否为可以null */
    public bool $allowsNull;

    /**
     * @var bool php内置标量
     * 检查类型是否是 PHP 中的内置类型。内置类型是除类、接口或特征之外的任何类型。
     */
    public bool $isBuiltin;

    /** @var string 类型名称 */
    public string $typeName;

    /** @var string */
    public string $arrayType='';

    /**
     * @var ReflectionAttribute<T>[]
     * @template T
     */
    public array $attributes;

}