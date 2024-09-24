<?php

declare(strict_types=1);

namespace App\Service\Processes;

use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;
use App\Service\Types\DbRunnerProcessPayload;
use App\Service\Types\DbRunnerProcessResponse;

class DbRunnerProcessService extends ProcessService
{
    public \SQLite3 $sqlite;

    public function __construct()
    {
        $sqlite = new \SQLite3(':memory:');
        $sqlite->busyTimeout(3000 /* milliseconds */);
        $sqlite->enableExceptions(true);

        $dateop = fn (string $format) => fn (string $date) => (int) date(
            $format,
            strtotime($date)
                ?: throw new \InvalidArgumentException("Failed to convert $date as $format."),
        );

        // MySQL-compatible functions
        $sqlite->createFunction('YEAR', $dateop('Y'), 1);
        $sqlite->createFunction('MONTH', $dateop('n'), 1);
        $sqlite->createFunction('DAY', $dateop('j'), 1);
        $sqlite->createFunction(
            'IF',
            fn (bool $condition, mixed $true, mixed $false) => $condition ? $true : $false,
            3,
        );

        $this->sqlite = $sqlite;
    }

    public function __destruct()
    {
        $this->sqlite->close();
    }

    public function main(object $input): object
    {
        if (!($input instanceof DbRunnerProcessPayload)) {
            throw new \InvalidArgumentException('Invalid input type');
        }

        $this->sqlite->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');

        // schema
        try {
            $this->sqlite->exec($input->getSchema());
        } catch (\SQLite3Exception) {
            throw new SchemaExecuteException($this->sqlite->lastErrorMsg());
        }

        // query
        try {
            $result = $this->sqlite->query($input->getQuery());
        } catch (\SQLite3Exception) {
            throw new QueryExecuteException($this->sqlite->lastErrorMsg());
        }

        if (\is_bool($result)) {
            throw new QueryExecuteException("Invalid query given: '{$input->getQuery()}'");
        }

        /**
         * @var array<array<string, mixed>> $resultArray
         */
        $resultArray = [];

        try {
            while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
                $resultArray[] = $row;
            }
        } finally {
            $result->finalize();
        }

        return new DbRunnerProcessResponse($resultArray);
    }
}
