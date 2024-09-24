<?php

declare(strict_types=1);

namespace App\Service\Types;

/**
 * The error that occurs when a process fails.
 */
readonly class DbRunnerProcessPayload
{
    public function __construct(
        private string $schema,
        private string $query,
    ) {
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
