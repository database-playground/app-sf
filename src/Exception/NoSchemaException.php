<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The exception that is thrown when a schema execution fails.
 */
class NoSchemaException extends HttpException
{
    public function __construct(int $questionId)
    {
        parent::__construct(500, "The question #$questionId does not have a schema.");
    }
}
