<?php

namespace Tests\ExampleClass;

class Meta extends \Majie\Converter\Converter
{
    /** @var ?int totalCount */
    public ?int $totalCount = null;

    /** @var ?int limit */
    public ?int $limit = null;

    /** @var ?string nextCursor */
    public ?string $nextCursor = null;
}