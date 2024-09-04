<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;
use App\Exception\TooManyResultsException;
use Doctrine\SqlFormatter\SqlFormatter;

const MAX_RESULT_SIZE = 1000;

/**
 * A class to run queries on a SQLite3 database.
 */
readonly class DbRunner
{
    private SqlFormatter $formatter;

    public function __construct()
    {
        $this->formatter = new SqlFormatter();
    }

    /**
     * Hash the given SQL statement to a hex string.
     *
     * Useful for caching. Normalization is applied.
     *
     * @param string $sql the SQL to hash
     *
     * @return string the hashed SHA-256 hex string
     */
    public function hashStatement(string $sql): string
    {
        return hash('sha3-256', $this->formatter->compress($sql));
    }

    /**
     * Run the query with SQLite3.
     *
     * For example:
     *
     * <code>
     *     $dbRunner = new DbRunner();
     *     $schema = "CREATE TABLE students (id INTEGER PRIMARY KEY, name TEXT)";
     *     $query = "SELECT * FROM students";
     *     $result = $dbRunner->runQuery($schema, $query);
     *     // $result is an array with the result of the query.
     *     // Example: [["id" => 1, "name" => "John"]]
     * </code>
     *
     * @param string $schema the schema to create the database
     * @param string $query  the query to run
     *
     * @return \Generator<int, array<string, mixed>, void, void> the result of the query
     *
     * @throws SchemaExecuteException  if the schema could not be executed
     * @throws QueryExecuteException   if the query could not be executed
     * @throws TooManyResultsException if the result size exceeds MAX_RESULT_SIZE
     */
    public function runQuery(string $schema, string $query): \Generator
    {
        $sqlite = new \SQLite3(':memory:');
        $sqlite->busyTimeout(3000 /* milliseconds */);
        $sqlite->enableExceptions(true);

        try {
            try {
                $sqlite->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
            } catch (\Exception) {
                throw new SchemaExecuteException($sqlite->lastErrorMsg());
            }

            try {
                $sqlite->exec($schema);
            } catch (\Exception) {
                throw new SchemaExecuteException($sqlite->lastErrorMsg());
            }

            try {
                $result = $sqlite->query($query);
            } catch (\Exception) {
                throw new QueryExecuteException($sqlite->lastErrorMsg());
            }

            if (\is_bool($result)) {
                throw new QueryExecuteException("invalid query given: '$query'");
            }

            try {
                $yieldCount = 0;
                while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
                    yield $row;
                    if (++$yieldCount >= MAX_RESULT_SIZE) {
                        throw new TooManyResultsException(MAX_RESULT_SIZE);
                    }
                }
            } finally {
                $result->finalize();
            }
        } finally {
            $sqlite->close();
        }
    }
}
