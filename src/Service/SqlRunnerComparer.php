<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ChallengeDto\CompareResult;
use App\Entity\SqlRunnerDto\SqlRunnerResult;

final readonly class SqlRunnerComparer
{
    /**
     * Compare this answer with user response and return the detailed information.
     *
     * @param SqlRunnerResult $answerResult the answer's query result
     * @param SqlRunnerResult $userResult   the user's query result
     *
     * @return CompareResult\CompareResult the comparison result
     */
    public static function compare(SqlRunnerResult $answerResult, SqlRunnerResult $userResult): CompareResult\CompareResult
    {
        if (0 === \count($answerResult->getColumns())) {
            return new CompareResult\EmptyAnswer();
        }
        if (0 === \count($userResult->getColumns())) {
            return new CompareResult\EmptyResult();
        }

        $answerColumns = $answerResult->getColumns();
        $userColumns = $userResult->getColumns();
        if ($answerColumns !== $userColumns) {
            return new CompareResult\ColumnDifferent();
        }

        $answerRows = $answerResult->getRows();
        $userRows = $userResult->getRows();
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
