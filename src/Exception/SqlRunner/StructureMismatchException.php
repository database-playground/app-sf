<?php

declare(strict_types=1);

namespace App\Exception\SqlRunner;

use Symfony\Component\HttpKernel\Exception\HttpException;

class StructureMismatchException extends HttpException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(
            500,
            'SQL runner returns a bad structure. You might need to upgrade it.',
            previous: $previous
        );
    }
}
