<?php

namespace Tests\ExampleClass;

use Majie\Converter\Converter;

class ChargeAmount extends Converter
{
    /** @var ?string currency */
    public ?string $currency = null;

    /** @var ?float amount */
    public ?float $amount = null;
}