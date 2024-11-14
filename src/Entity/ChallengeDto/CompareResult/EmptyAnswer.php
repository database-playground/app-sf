<?php

declare(strict_types=1);

namespace App\Entity\ChallengeDto\CompareResult;

use Symfony\Component\Translation\TranslatableMessage;

use function Symfony\Component\Translation\t;

readonly class EmptyAnswer implements CompareResult
{
    public function correct(): bool
    {
        return false;
    }

    public function reason(): TranslatableMessage
    {
        return t('challenge.compare-result.empty-answer');
    }
}
