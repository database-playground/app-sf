<?php

namespace App\Service;

use Dbrunner\V1\RunQueryResponse;

readonly class QueryResponse implements \JsonSerializable
{
    public function __construct(
        public string $id,
    )
    {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}