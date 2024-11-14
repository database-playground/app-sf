<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ChallengeDto\CompareResult;
use App\Entity\ChallengeDto\QueryResultDto;

readonly class DbRunnerComparer
{
    /**
     * Compare this answer with user response and return the detailed information.
     *
     * @param QueryResultDto $answerResult the answer's query result
     * @param QueryResultDto $userResult   the user's query result
     *
     * @return CompareResult\CompareResult the comparison result
     */
    public static function compare(QueryResultDto $answerResult, QueryResultDto $userResult): CompareResult\CompareResult
    {
        if (0 === \count($answerResult->getResult())) {
            return new CompareResult\EmptyAnswer();
        }
        if (0 === \count($userResult->getResult())) {
            return new CompareResult\EmptyResult();
        }

        $answerColumns = $answerResult->getResult()[0];
        $userColumns = $userResult->getResult()[0];
        if ($answerColumns !== $userColumns) {
            return new CompareResult\ColumnDifferent();
        }

        $answerRows = \array_slice($answerResult->getResult(), 1);
        $userRows = \array_slice($userResult->getResult(), 1);
        if (\count($answerRows) !== \count($userRows)) {
            return new CompareResult\RowUnmatched(
                expected: \count($answerRows),
                actual: \count($userRows),
            );
        }

        for ($i = 0; $i < \count($answerRows); ++$i) {
            if ($answerRows[$i] !== $userRows[$i]) {
                return new CompareResult\RowDifferent(
                    row: $i + 1,
                );
            }
        }

        return new CompareResult\Same();
    }
}
