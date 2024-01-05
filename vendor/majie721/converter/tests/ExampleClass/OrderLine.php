<?php

namespace Tests\ExampleClass;

use Majie\Converter\Converter;

class OrderLine extends Converter
{
    public ?string $lineNumber = null;

    public mixed $statusDate = null;

    public ?Item $item = null;

    public ?Charges $charges = null;

    public ?OrderLineQuantity $orderLineQuantity = null;

    public ?OrderLineStatuses $orderLineStatuses = null;

    public ?Fulfillment $fulfillment = null;

    public ?OrderStatusEnum $status;


}