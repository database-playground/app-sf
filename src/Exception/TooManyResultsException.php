<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The exception that is thrown when a query returns too many results.
 */
class TooManyResultsException extends BadRequestHttpException
{
    public function __construct(int $limit)
    {
        parent::__construct("The query returned too many results. The limit is $limit.");
    }
}
