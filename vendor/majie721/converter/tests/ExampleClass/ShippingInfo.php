<?php

namespace Tests\ExampleClass;

class ShippingInfo extends \Majie\Converter\Converter
{
    /** @var ?string phone */
    public ?string $phone = null;

    /** @var mixed estimatedDeliveryDate */
    public mixed $estimatedDeliveryDate = null;

    /** @var mixed estimatedShipDate */
    public mixed $estimatedShipDate = null;

    /** @var ?string methodCode */
    public ?string $methodCode = null;


    public ?PostalAddress $postalAddress = null;
}