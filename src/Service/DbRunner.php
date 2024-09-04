<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;
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
     * @return \Generator<array<string, mixed>> the result of the query
     *
     * @throws SchemaExecuteException if the schema could not be executed
     * @throws QueryExecuteException  if the query could not be executed
     */
    public function runQuery(string $schema, string $query): \Generator
    {
        $sqlite = new \SQLite3(':memory:');
        $sqlite->busyTimeout(3000 /* milliseconds */);

        try {
            if (!$sqlite->exec($schema)) {
                throw new SchemaExecuteException($sqlite->lastErrorMsg());
            }

            $result = $sqlite->query($query);
            if (!$result) {
                throw new QueryExecuteException($sqlite->lastErrorMsg());
            }

            $yieldCount = 0;
            while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
                yield $row;
                if (++$yieldCount >= MAX_RESULT_SIZE) {
                    error_log('Result size exceeds the maximum of '.MAX_RESULT_SIZE);
                    break;
                }
            }
        } finally {
            if (isset($result) && !\is_bool($result)) {
                $result->finalize();
            }

            $sqlite->close();
        }
    }
}
