<?php

namespace Tests\ExampleClass;

class Charge extends \Majie\Converter\Converter
{
    /** @var ?string chargeType */
    public ?string $chargeType = null;

    /** @var ?string chargeName */
    public ?string $chargeName = null;

    public ?ChargeAmount $chargeAmount = null;

    public ?Tax $tax = null;
}