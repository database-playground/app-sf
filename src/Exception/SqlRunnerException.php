<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SqlRunnerException extends HttpException
{
    public function __construct(string $code, string $message, ?\Throwable $previous = null)
    {
        parent::__construct(500, "{$code}: {$message}", previous: $previous);
    }
}
