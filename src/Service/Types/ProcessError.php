<?php

declare(strict_types=1);

namespace App\Service\Types;

/**
 * The error that occurs when a process fails.
 */
readonly class ProcessError
{
    public function __construct(
        private \Throwable $throwable,
    ) {
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }

    /**
     * Rethrows the error.
     *
     * @throws \Throwable
     */
    public function rethrow(): void
    {
        throw $this->throwable;
    }
}
