<?php

declare(strict_types=1);

namespace App\Exception\SqlRunner;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RunnerException extends HttpException
{
    public function __construct(string $code, string $message)
    {
        parent::__construct(500, "$code: $message");
    }
}
