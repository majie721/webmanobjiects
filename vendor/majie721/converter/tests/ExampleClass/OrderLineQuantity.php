<?php

namespace Tests\ExampleClass;

class OrderLineQuantity extends \Majie\Converter\Converter
{
    /** @var ?string unitOfMeasurement */
    public ?string $unitOfMeasurement = null;

    /** @var ?string amount */
    public ?string $amount = null;
}