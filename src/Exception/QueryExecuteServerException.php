<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The exception for the case when the query execution fails on the server.
 * (Not the fault of the client)
 */
class QueryExecuteServerException extends HttpException
{
    public function __construct(string $message)
    {
        parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
    }
}
