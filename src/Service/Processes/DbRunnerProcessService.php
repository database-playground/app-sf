<?php

declare(strict_types=1);

namespace App\Service\Processes;

use App\Entity\ChallengeDto\QueryResultDto;
use App\Service\Types\DbRunnerProcessPayload;
use App\Service\Types\SchemaDatabase;

class DbRunnerProcessService extends ProcessService
{
    public function main(object $input): object
    {
        if (!($input instanceof DbRunnerProcessPayload)) {
            throw new \InvalidArgumentException('Invalid input type');
        }

        $db = SchemaDatabase::get($input->schema);
        $sqliteResult = $db->query($input->query);
        $queryResult = $this->transformResult($sqliteResult);
        $sqliteResult->finalize();

        return $queryResult;
    }

    private function transformResult(\SQLite3Result $result): QueryResultDto
    {
        /**
         * @var array<array<int, string>> $columnsRow
         */
        $columnsRow = [];

        for ($i = 0; $i < $result->numColumns(); ++$i) {
            $columnsRow[] = $result->columnName($i);
        }

        /**
         * @var array<array<int, string>> $rows
         */
        $rows = [];

        while ($rawRow = $result->fetchArray(\SQLITE3_ASSOC)) {
            $row = [];
            foreach ($rawRow as $value) {
                $row[] = match (true) {
                    null === $value => 'NULL',
                    \is_string($value) => $value,
                    \is_bool($value) => $value ? 'TRUE' : 'FALSE',
                    is_numeric($value) => (string) $value,
                    default => '<unsupported type: '.\gettype($value).'>',
                };
            }
            $rows[] = $row;
        }

        /**
         * @var array<array<int, string>> $merged
         */
        $merged = array_merge([$columnsRow], $rows);

        return (new QueryResultDto())->setResult($merged);
    }
}
