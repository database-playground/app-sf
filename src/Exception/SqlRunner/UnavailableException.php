<?php

declare(strict_types=1);

namespace App\Exception\SqlRunner;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnavailableException extends HttpException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(
            503,
            'SQL Runner is temporarily unavailable. Please try again later.',
            previous: $previous
        );
    }
}
