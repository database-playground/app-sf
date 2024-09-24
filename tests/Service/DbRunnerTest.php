<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\QueryExecuteException;
use App\Exception\ResourceException;
use App\Exception\SchemaExecuteException;
use App\Exception\TimedOutException;
use App\Service\DbRunner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DbRunnerTest extends TestCase
{
    /**
     * @return array{string, string}[]
     */
    public static function hashProvider(): array
    {
        return [
            [
                'SELECT * FROM students',
                'SELECT * FROM students',
            ],
            [
                'SELECT * FROM students WHERE id = 1',
                'SELECT * FROM students WHERE id = 1',
            ],
            [
                'SELECT * FROM students WHERE id = 1; -- comment',
                'SELECT * FROM students WHERE id = 1;',
            ],
            [
                "SELECT * FROM students WHERE id = 1; -- comment\n",
                'SELECT * FROM students WHERE id = 1;',
            ],
            [
                "SELECT *, aaa FROM students WHERE id = 1; -- comment\n",
                'SELECT *, aaa FROM students WHERE id = 1;',
            ],
            [
                "SELECT * FROM students;\nSELECT * FROM teachers;",
                'SELECT * FROM students; SELECT * FROM teachers;',
            ],
            [
                'SELECT *     FROM   students',
                'SELECT * FROM students',
            ],
            // WIP: SqlFormatter does not uppercase keywords
            // [
            //    "seLect * fRom students",
            //     "SELECT * FROM students;"
            // ]
        ];
    }

    /**
     * @return string[]
     */
    public static function hashInvalidProvider(): array
    {
        return [
            'SELECT *',
            'select *',
            'SELECT * FROM students id = 1; -- comment',
            'SELECT * FROM students WHERE id 1 -- comment',
            'SELECT * students WHERE id = 1 -- comment',
            'SELECT * FROM WHERE id = 1 -- comment',
        ];
    }

    /**
     * @return array<array{string, string, array<array<string, mixed>>|null, class-string<\Throwable>|null}>
     */
    public static function runQueryProvider(): array
    {
        return [
            [
                "CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test (name) VALUES ('Alice');
                INSERT INTO test (name) VALUES ('Bob');",
                'SELECT * FROM test;',
                [
                    ['id' => 1, 'name' => 'Alice'],
                    ['id' => 2, 'name' => 'Bob'],
                ], /* result */
                null, /* exception */
            ],
            [
                "CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test (name) VALUES ('Alice');
                INSERT INTO test (name) VALUES ('Bob');",
                '',
                [], /* result */
                QueryExecuteException::class, /* exception */
            ],
            [
                "CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test (name) VALUES ('Alice');
                INSERT INTO test (name) VALUES ('Bob');",
                "UPDATE test SET name = 'Charlie' WHERE id = 1;",
                null, /* result */
                null, /* exception */
            ],
            [
                "CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test (name) VALUES ('Alice');
                INSERT INTO test (name) VALUES ('Bob');",
                "UPDATE test SET name = 'Charlie' WHERE id = 1 RETURNING *;",
                [
                    ['id' => 1, 'name' => 'Charlie'],
                ], /* result */
                null, /* exception */
            ],
            [
                "CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test (name) VALUES ('Alice');
                INSERT INTO test (name) VALUES ('Bob');",
                'SELECT * FROM unknown_table;',
                null, /* result */
                QueryExecuteException::class, /* exception */
            ],
            [
                "CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test (name) VALUES ('Alice');
                INSERT INTO test (name) VALUES ('Bob');",
                'SELECT * FROM test WHERE id = @1;',
                null, /* result */
                null, /* exception */
            ],
            [
                "CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test (name) VALUES ('Alice');
                INSERT INTO test (name) VALUES ('Bob');",
                "SELECT * FROM test WHERE id = ':D)D)D))D)D)D)D)D;",
                null, /* result */
                QueryExecuteException::class, /* exception */
            ],
            [
                'CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                ABCDEFG;',
                'SELECT * FROM test;',
                null, /* result */
                SchemaExecuteException::class, /* exception */
            ],
            [
                'CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test VALUES (1, NULL);',
                'SELECT * FROM test;',
                [
                    ['id' => 1, 'name' => null],
                ], /* result */
                null, /* exception */
            ],
            [
                'CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test VALUES (1, 1.23);',
                'SELECT * FROM test;',
                [
                    ['id' => 1, 'name' => 1.23],
                ], /* result */
                null, /* exception */
            ],
            [
                "CREATE TABLE test (
                    id INTEGER PRIMARY KEY,
                    name TEXT
                );

                INSERT INTO test VALUES (1, x'68656c6c6f');",
                'SELECT * FROM test;',
                [
                    ['id' => 1, 'name' => 'hello'],
                ],  /* result */
                null, /* exception */
            ],
        ];
    }

    #[DataProvider('hashProvider')]
    public function testHashStatement(string $leftStmt, string $rightStmt): void
    {
        $dbrunner = new DbRunner();

        $leftHash = $dbrunner->hashStatement($leftStmt);
        $rightHash = $dbrunner->hashStatement($rightStmt);

        $this->assertEquals($leftHash, $rightHash);
    }

    #[DataProvider('hashProvider')]
    public function testHashInvalidStatement(string $invalidStmt): void
    {
        $this->expectNotToPerformAssertions();

        $dbrunner = new DbRunner();

        // don't throw an exception
        $dbrunner->hashStatement($invalidStmt);
    }

    /**
     * @param ?array<array<string, mixed>> $expect
     * @param ?class-string<\Throwable>    $exception
     */
    #[DataProvider('runQueryProvider')]
    public function testRunQuery(string $schema, string $query, ?array $expect, ?string $exception): void
    {
        $dbrunner = new DbRunner();

        if (null !== $exception) {
            $this->expectException($exception);
        } elseif (!$expect) {
            $this->expectNotToPerformAssertions();
        }

        $generator = $dbrunner->runQuery($schema, $query);

        if ($expect) {
            foreach ($generator as $idx => $actual) {
                $this->assertEquals($expect[$idx], $actual);
            }
        }
    }

    public function testRunQueryCte(): void
    {
        $dbrunner = new DbRunner();

        $schema = "CREATE TABLE test (
            id INTEGER PRIMARY KEY,
            name TEXT
        );

        INSERT INTO test (name) VALUES ('Alice');
        INSERT INTO test (name) VALUES ('Bob');";
        $query = 'WITH RECURSIVE cte (n) AS (
            SELECT 1
            UNION ALL
            SELECT n + 1 FROM cte
        )
        SELECT * FROM cte;';

        $this->expectException(ResourceException::class);
        $generator = $dbrunner->runQuery($schema, $query);

        foreach ($generator as $idx => $actual) {
        }
    }

    public function testRunQueryBigPayload(): void
    {
        $dbrunner = new DbRunner(timeout: 1);

        $schema = '';
        $query = 'SELECT 1,2,3,4,5,6,randomblob(1000000000);';

        $this->expectException(TimedOutException::class);
        $generator = $dbrunner->runQuery($schema, $query);

        foreach ($generator as $idx => $actual) {
        }
    }

    public function testRunQueryYear(): void
    {
        $dbrunner = new DbRunner();

        $this->assertEquals([['year("2021-01-01")' => 2021]], $dbrunner->runQuery('', 'SELECT year("2021-01-01")'));
    }

    public function testRunQueryMonth(): void
    {
        $dbrunner = new DbRunner();

        $this->assertEquals([['month("2021-01-01")' => 1]], $dbrunner->runQuery('', 'SELECT month("2021-01-01")'));
    }

    public function testRunQueryDay(): void
    {
        $dbrunner = new DbRunner();

        $this->assertEquals([['day("2021-01-01")' => 1]], $dbrunner->runQuery('', 'SELECT day("2021-01-01")'));
    }

    public function testRunQueryIf(): void
    {
        $dbrunner = new DbRunner();

        $this->assertEquals([['if(1, 2, 3)' => 2]], $dbrunner->runQuery('', 'SELECT if(1, 2, 3)'));
        $this->assertEquals([['if(0, 2, 3)' => 3]], $dbrunner->runQuery('', 'SELECT if(0, 2, 3)'));
    }

    public function testRunQuerySum(): void
    {
        $dbrunner = new DbRunner();

        $this->assertEquals([['sum(n)' => 6]], $dbrunner->runQuery('', 'SELECT sum(n) FROM (SELECT 1 AS n UNION SELECT 2 AS n UNION SELECT 3 AS n)'));
    }
}
