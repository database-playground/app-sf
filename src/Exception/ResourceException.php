<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The exception that is thrown when a query consumes too many resources.
 */
class ResourceException extends BadRequestHttpException
{
    public function __construct()
    {
        parent::__construct('Resource exhausted or process crashed.');
    }
}
