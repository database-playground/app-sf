<?php

declare(strict_types=1);

namespace App\Exception\SqlRunner;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The exception that is thrown when a schema execution fails.
 */
class SchemaExecuteException extends HttpException
{
    public function __construct(string $message)
    {
        parent::__construct(500, $message);
    }
}
