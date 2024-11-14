<?php

declare(strict_types=1);

namespace App\Entity\ChallengeDto\CompareResult;

use Symfony\Component\Translation\TranslatableMessage;

use function Symfony\Component\Translation\t;

readonly class RowUnmatched implements CompareResult
{
    /**
     * @param int $expected the expected row number
     * @param int $actual   the actual row number
     */
    public function __construct(
        public int $expected,
        public int $actual,
    ) {
    }

    public function correct(): bool
    {
        return false;
    }

    public function reason(): TranslatableMessage
    {
        return t('challenge.compare-result.row-unmatched', [
            '%expected%' => $this->expected,
            '%actual%' => $this->actual,
        ]);
    }
}
