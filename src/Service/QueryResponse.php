<?php

declare(strict_types=1);

namespace App\Service;

readonly class QueryResponse implements \JsonSerializable
{
    public function __construct(
        public string $id,
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
