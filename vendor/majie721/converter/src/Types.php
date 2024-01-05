<?php

namespace Majie\Converter;

class Types
{
    public const Array  = 'array';
    public const String = 'string';
    public const Float  = 'float';
    public const Int    = 'int';
    public const Bool   = 'bool';
    public const False  = 'false';
    public const True   = 'true';
    public const Null   = 'null';
    public const Mixed  = 'mixed';
    public const Object = 'object';

    public static function allowFill(): array
    {
        return [self::Array,self::Mixed,self::String,self::Float,self::Int,self::Bool,self::False,self::True,self::Null];
    }


    public static function enableArrayType($type):array{
        $types =  [self::String,self::Float,self::Int,self::Bool,self::False,self::True,self::Null];
        return in_array($type,$types);
    }


}
