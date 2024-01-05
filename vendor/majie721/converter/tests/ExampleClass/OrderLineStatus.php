<?php

namespace Tests\ExampleClass;

class OrderLineStatus extends \Majie\Converter\Converter
{
    /** @var ?string status */
    public ?string $status = null;

    public ?StatusQuantity $statusQuantity = null;
}