<?php

declare(strict_types=1);

namespace App\Exception\SqlRunner;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The exception that is thrown when a query execution fails.
 */
class QueryExecuteException extends BadRequestHttpException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
