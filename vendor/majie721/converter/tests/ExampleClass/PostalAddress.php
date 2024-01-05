<?php

namespace Tests\ExampleClass;

class PostalAddress extends \Majie\Converter\Converter
{
    /** @var ?string name */
    public ?string $name = null;

    /** @var ?string address1 */
    public ?string $address1 = null;

    /** @var ?string address2 */
    public ?string $address2 = null;

    /** @var ?string city */
    public ?string $city = null;

    /** @var ?string state */
    public ?string $state = null;

    /** @var ?string postalCode */
    public ?string $postalCode = null;

    /** @var ?string country */
    public ?string $country = null;

    /** @var ?string addressType */
    public ?string $addressType = null;
}