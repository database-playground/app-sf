<?php

declare(strict_types=1);

namespace App\Entity\ChallengeDto\CompareResult;

use Symfony\Component\Translation\TranslatableMessage;

use function Symfony\Component\Translation\t;

readonly class RowDifferent implements CompareResult
{
    /**
     * @param int $row the row number that is different
     */
    public function __construct(public int $row)
    {
    }

    public function correct(): bool
    {
        return false;
    }

    public function reason(): TranslatableMessage
    {
        return t('challenge.compare-result.row-different', [
            '%row%' => $this->row,
        ]);
    }
}
