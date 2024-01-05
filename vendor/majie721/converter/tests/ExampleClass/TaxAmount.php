<?php

namespace Tests\ExampleClass;

class TaxAmount extends \Majie\Converter\Converter
{
    /** @var ?string currency */
    public ?string $currency = null;

    /** @var ?float amount */
    public ?float $amount = null;

}