<?php

namespace Tests\ExampleClass;

use Majie\Converter\Converter;

class Tax extends Converter
{
    /** @var ?string taxName */
    public ?string $taxName = null;
    public ?TaxAmount $taxAmount = null;
}