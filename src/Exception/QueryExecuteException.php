<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

