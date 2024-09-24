<?php

declare(strict_types=1);

namespace App\Service\Types;

/**
 * The error that occurs when a process fails.
 */
readonly class DbRunnerProcessResponse
{
    /**
     * @param array<array<string, mixed>> $result
     */
    public function __construct(
        private array $result,
    ) {
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
