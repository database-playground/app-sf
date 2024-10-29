<?php

declare(strict_types=1);

namespace App\Service\Types;

use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;

/**
 * The SQLite 3 database for caching and duplicating the schema.
 */
readonly class SchemaDatabase
{
    private function __construct(private \SQLite3 $db)
    {
    }

    public static function get(string $schema): self
    {
        $filename = self::getSchemaSqlFilename($schema);

        self::initialize($filename, $schema);

        return new self(self::setUp(new \SQLite3($filename, \SQLITE3_OPEN_READONLY)));
    }

    public function __destruct()
    {
        $this->db->close();
    }

    /**
     * Execute the query and return the result.
     *
     * @param string $query the query to execute
     *
     * @return \SQLite3Result the result of the query
     *
     * @throws QueryExecuteException if the query could not be executed
     */
    public function query(string $query): \SQLite3Result
    {
        try {
            $result = $this->db->query($query);
        } catch (\Throwable) {
            throw new QueryExecuteException($this->db->lastErrorMsg());
        }

        if (\is_bool($result)) {
            throw new QueryExecuteException("Invalid query given: '$query'");
        }

        return $result;
    }

    private static function setUp(\SQLite3 $db): \SQLite3
    {
        $db->busyTimeout(3000 /* milliseconds */);
        $db->enableExceptions(true);

        $dateop = fn (string $format) => fn (string $date) => (int) date(
            $format,
            ($datestr = strtotime($date)) !== false
                ? $datestr
                : throw new \InvalidArgumentException("Failed to convert $date as $format."),
        );

        // MySQL-compatible functions
        $db->createFunction('YEAR', $dateop('Y'), 1, SQLITE3_DETERMINISTIC);
        $db->createFunction('MONTH', $dateop('n'), 1, SQLITE3_DETERMINISTIC);
        $db->createFunction('DAY', $dateop('j'), 1, SQLITE3_DETERMINISTIC);
        $db->createFunction(
            'IF',
            fn (bool $condition, mixed $true, mixed $false) => $condition ? $true : $false,
            3,
            SQLITE3_DETERMINISTIC
        );

        return $db;
    }

    /**
     * Initialize the database and return the filename to the schema sqlite3.
     *
     * It does nothing if the file already exists.
     *
     * @param string $filename the filename to the schema sqlite3
     * @param string $schema   the schema to initialize
     *
     * @throws SchemaExecuteException if the schema could not be executed
     */
    private static function initialize(string $filename, string $schema): void
    {
        if (file_exists($filename)) {
            return;
        }

        $db = new \SQLite3($filename);
        $db->enableExceptions(true);

        try {
            $db->exec('BEGIN EXCLUSIVE');
            $db->exec($schema);
            $db->exec('COMMIT');
            $db->close();
        } catch (\Throwable) {
            $lastErrorMessage = $db->lastErrorMsg();

            // remove the file if the schema could not be executed
            $db->close();
            unlink($filename);

            throw new SchemaExecuteException($lastErrorMessage);
        }
    }

    private static function getSchemaSqlFilename(string $schema): string
    {
        $tmpdir = sys_get_temp_dir();
        $schemaHash = hash('sha3-256', $schema);

        return "$tmpdir/dbrunner_$schemaHash.sql";
    }
}
