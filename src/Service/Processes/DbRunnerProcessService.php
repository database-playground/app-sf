<?php

declare(strict_types=1);

namespace App\Service\Processes;

use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;
use App\Service\Types\DbRunnerProcessPayload;
use App\Service\Types\DbRunnerProcessResponse;

class DbRunnerProcessService extends ProcessService
{
    public function main(object $input): object
    {
        if (!($input instanceof DbRunnerProcessPayload)) {
            throw new \InvalidArgumentException('Invalid input type');
        }

        $db = $this->getPreparedDatabase($input->getSchema());
        try {
            // query
            try {
                $result = $db->query($input->getQuery());
            } catch (\SQLite3Exception) {
                throw new QueryExecuteException($db->lastErrorMsg());
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
        } finally {
            $db->close();
        }

        return new DbRunnerProcessResponse($resultArray);
    }

    /**
     * Get the prepared isolated database that contains the schema.
     *
     * You should close the database after using it.
     *
     * @param string $schema the schema to run
     *
     * @return \SQLite3 the prepared SQLite3 instance
     */
    private function getPreparedDatabase(string $schema): \SQLite3
    {
        $schemaDbFile = $this->createDatabase($schema);
        $schemaDb = $this->createSqliteInstance($schemaDbFile);

        try {
            $isolatedDb = $this->createSqliteInstance(':memory:');

            $schemaDb->backup($isolatedDb);

            return $isolatedDb;
        } finally {
            $schemaDb->close();
        }
    }

    /**
     * Create the prepared database with the schema.
     *
     * It returns the filename containing the database.
     * If the database already exists, it will return the filename only.
     *
     * @param string $schema the schema to run
     *
     * @return string the filename containing the database
     *
     * @throws SchemaExecuteException if the schema could not be executed
     */
    private function createDatabase(string $schema): string
    {
        $tmpdir = sys_get_temp_dir();
        $hash = hash('sha3-256', $schema);
        $dbfile = "$tmpdir/dbrunner_$hash.db";

        if (file_exists($dbfile)) {
            return $dbfile;
        }

        $db = $this->createSqliteInstance($dbfile);

        try {
            $db->exec($schema);
        } catch (\SQLite3Exception) {
            throw new SchemaExecuteException($db->lastErrorMsg());
        } finally {
            $db->close();
        }

        return $dbfile;
    }

    /**
     * Create a SQLite instance in memory with the schema.
     *
     * @param string $filename the filename to create the SQLite instance
     *
     * @return \SQLite3 the SQLite instance
     */
    private function createSqliteInstance(string $filename): \SQLite3
    {
        $sqlite = new \SQLite3($filename);
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

        $sqlite->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');

        return $sqlite;
    }
}
