<?php

declare(strict_types=1);

namespace App\Entity\ChallengeDto\CompareResult;

use Symfony\Component\Translation\TranslatableMessage;

use function Symfony\Component\Translation\t;

/**
 * There is no different.
 */
readonly class Same implements CompareResult
{
    public function correct(): bool
    {
        return true;
    }

    public function reason(): TranslatableMessage
    {
        return t('challenge.compare-result.same');
    }
}
