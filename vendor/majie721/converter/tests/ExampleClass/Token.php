<?php

namespace Tests\ExampleClass;

use Majie\Converter\Converter;

class Token extends Converter
{
    public ?string $access_token = null;

    public ?string $token_type = null;

    public ?int $expires_in = null;
}