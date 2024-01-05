<?php

namespace Tests\ExampleClass;

use Majie\Converter\Converter;

class Fulfillment extends Converter
{
    /** @var ?string fulfillmentOption */
    public ?string $fulfillmentOption = null;

    /** @var ?string shipMethod */
    public ?string $shipMethod = null;

    /** @var mixed pickUpDateTime */
    public mixed $pickUpDateTime = null;
}