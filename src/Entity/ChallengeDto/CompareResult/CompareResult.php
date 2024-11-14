<?php

declare(strict_types=1);

namespace App\Entity\ChallengeDto\CompareResult;

use Symfony\Component\Translation\TranslatableMessage;

/**
 * The result of a comparison.
 */
interface CompareResult
{
    public function correct(): bool;

    public function reason(): TranslatableMessage;
}
