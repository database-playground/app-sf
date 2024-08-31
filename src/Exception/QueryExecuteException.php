<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The exception that is thrown when a query execution fails.
 */
class QueryExecuteException extends BadRequestHttpException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
