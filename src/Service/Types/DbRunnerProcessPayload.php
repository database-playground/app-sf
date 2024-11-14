<?php

declare(strict_types=1);

namespace App\Service\Types;

/**
 * The payload to the DbRunner process.
 */
readonly class DbRunnerProcessPayload
{
    public function __construct(
        public string $schema,
        public string $query,
    ) {
    }
}
