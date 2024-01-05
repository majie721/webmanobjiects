<?php

namespace Majie\Converter\Exceptions;

use Majie\Converter\PropertyInfo;
use Throwable;

class ConverterClassException extends \Exception
{
    private string $className;
    private PropertyInfo $property;
    private mixed $value;

    public function __construct(string $message = "",string $className='',?PropertyInfo $property=null,mixed $value=null, ?Throwable $previous = null,$code=0)
    {
        $this->className = $className;
        $this->property = $property;
        $this->value = $value;
        parent::__construct($message, $code, $previous);
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName(): ?PropertyInfo
    {
        return $this->property;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}