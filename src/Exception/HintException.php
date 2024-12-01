<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class HintException extends HttpException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(500, "Failed to hint: {$previous?->getMessage()}", previous: $previous);
    }
}
