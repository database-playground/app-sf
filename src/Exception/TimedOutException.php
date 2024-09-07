<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The exception that is thrown when a query consumes too many resources.
 */
class TimedOutException extends BadRequestHttpException
{
    public function __construct(float $timeout)
    {
        parent::__construct("Your query timed out (over {$timeout}s). Please try again with a smaller query.");
    }
}
