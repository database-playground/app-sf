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
     * @return array<array{string, string, array<array<array-key, mixed>>|null, class-string<\Throwable>|null}>
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
                    ['id', 'name'],
                    ['1', 'Alice'],
                    ['2', 'Bob'],
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
                QueryExecuteException::class, /* exception */
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
                    ['id', 'name'],
                    ['1', 'Charlie'],
                ], /* result */
                QueryExecuteException::class, /* exception */
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
                    ['id', 'name'],
                    ['1', 'NULL'],
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
                    ['id', 'name'],
                    ['1', '1.23'],
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
                    ['id', 'name'],
                    ['1', 'hello'],
                ],  /* result */
                null, /* exception */
            ],
            [
                '',
                'SELECT 1;',
                [
                    ['1'],
                    ['1'],
                ], /* result */
                null, /* exception */
            ],
            [
                "CREATE TABLE records (
    RecordID INTEGER PRIMARY KEY,   -- Assuming a unique identifier for each record
    ClassNo varchar(5) NOT NULL,    -- Stores the class number as a string
    YMD DATE NOT NULL,              -- Stores the date in 'YYYY-MM-DD' format
    Leave INTEGER DEFAULT 0,        -- Stores the leave count for personal leave
    SickLeave INTEGER DEFAULT 0,    -- Stores the leave count for sick leave
    PublicLeave INTEGER DEFAULT 0,  -- Stores the leave count for public leave
    Absent INTEGER DEFAULT 0        -- Stores the count for absences
);

INSERT INTO records (RecordID, ClassNo, YMD, Leave, SickLeave, PublicLeave, Absent) VALUES
    (1, '101A', '2018-03-15', 2, 1, 0, 0),
    (2, '101B', '2018-03-16', 0, 0, 1, 1),
    (3, '102A', '2018-03-17', 1, 0, 2, 0),
    (4, '101A', '2018-04-15', 0, 1, 0, 1),
    (5, '102B', '2018-05-20', 3, 0, 0, 0),
    (6, '101B', '2018-06-25', 0, 2, 0, 1),
    (7, '101C', '2018-07-10', 1, 1, 1, 0),
    (8, '103A', '2018-08-30', 0, 0, 3, 1),
    (9, '101A', '2019-09-01', 2, 1, 0, 1),  -- Different year for variety
    (10, '102A', '2018-10-11', 0, 0, 1, 0);",
                'SELECT
    LEFT(records.ClassNo, 3) AS 班級,
    SUM(records.Leave) AS 事假總計,
    SUM(records.SickLeave) AS 病假總計,
    SUM(records.PublicLeave) AS 公假總計,
    SUM(records.Absent) AS 曠課總計
FROM
    records
WHERE
    YEAR(YMD) = 2018
group BY
    LEFT(records.ClassNo, 3)
',
                [
                    ['班級', '事假總計', '病假總計', '公假總計', '曠課總計'],
                    ['101', '3', '5', '2', '3'],
                    ['102', '4', '0', '3', '0'],
                    ['103', '0', '0', '3', '1'],
                ], /* result */
                null, /* exception */
            ],
        ];
    }

    /**
     * @dataProvider hashProvider
     */
    public function testHashStatement(string $leftStmt, string $rightStmt): void
    {
        $dbrunner = new DbRunner();

        $leftHash = $dbrunner->hashStatement($leftStmt);
        $rightHash = $dbrunner->hashStatement($rightStmt);

        self::assertEquals($leftHash, $rightHash);
    }

    /**
     * @dataProvider hashProvider
     */
    public function testHashInvalidStatement(string $invalidStmt): void
    {
        $this->expectNotToPerformAssertions();

        $dbrunner = new DbRunner();

        // don't throw an exception
        $dbrunner->hashStatement($invalidStmt);
    }

    /**
     * @dataProvider runQueryProvider
     *
     * @param ?array<array<string, mixed>> $expect
     * @param ?class-string<\Throwable>    $exception
     *
     * @throws \Throwable
     */
    public function testRunQuery(string $schema, string $query, ?array $expect, ?string $exception): void
    {
        $dbrunner = new DbRunner();

        if (null !== $exception) {
            $this->expectException($exception);
        } elseif (null === $expect) {
            $this->expectNotToPerformAssertions();
        }

        $result = $dbrunner->runQuery($schema, $query);
        if (null === $expect) {
            return;
        }

        self::assertEquals($expect, $result->getResult());
    }

    public function testRunQueryCte(): void
    {
        $dbrunner = new DbRunner(5);

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
        $dbrunner->runQuery($schema, $query);
    }

    public function testRunQueryBigPayload(): void
    {
        $dbrunner = new DbRunner(timeout: 1);

        $schema = '';
        $query = 'SELECT 1,2,3,4,5,6,randomblob(1000000000);';

        $this->expectException(TimedOutException::class);
        $dbrunner->runQuery($schema, $query);
    }

    public function testRunQueryYear(): void
    {
        $dbrunner = new DbRunner();

        $result = $dbrunner->runQuery('', 'SELECT year("2021-01-01")');
        self::assertEquals([['year("2021-01-01")'], ['2021']], $result->getResult());
    }

    public function testRunQueryMonth(): void
    {
        $dbrunner = new DbRunner();

        $result = $dbrunner->runQuery('', 'SELECT month("2021-01-01")');
        self::assertEquals([['month("2021-01-01")'], ['1']], $result->getResult());
    }

    public function testRunQueryDay(): void
    {
        $dbrunner = new DbRunner();

        $result = $dbrunner->runQuery('', 'SELECT day("2021-01-01")');
        self::assertEquals([['day("2021-01-01")'], ['1']], $result->getResult());
    }

    public function testRunQueryIf(): void
    {
        $dbrunner = new DbRunner();

        $result = $dbrunner->runQuery('', 'SELECT if(1, 2, 3)');
        self::assertEquals([['if(1, 2, 3)'], ['2']], $result->getResult());

        $result = $dbrunner->runQuery('', 'SELECT if(0, 2, 3)');
        self::assertEquals([['if(0, 2, 3)'], ['3']], $result->getResult());
    }

    public function testRunQueryLeft(): void
    {
        $dbrunner = new DbRunner();

        $testcases = [
            'left("abcdef", 3)' => 'abc',
            'left("1234567", 8)' => '1234567',
            'left("hello", 2)' => 'he',
            'left("hello", 0)' => '',
            'left("hello", 6)' => 'hello',
        ];

        foreach ($testcases as $query => $expected) {
            $result = $dbrunner->runQuery('', 'SELECT '.$query);
            self::assertEquals([[$query], [$expected]], $result->getResult());
        }

        $result = $dbrunner->runQuery('', 'SELECT left(c, 6) FROM (SELECT \'hello\' AS c)');
        self::assertEquals([['left(c, 6)'], ['hello']], $result->getResult());
    }

    public function testRunQuerySum(): void
    {
        $dbrunner = new DbRunner();

        $result = $dbrunner->runQuery('', 'SELECT sum(1)');
        self::assertEquals([['sum(1)'], ['1']], $result->getResult());
    }

    public function testSchemaCache(): void
    {
        $dbrunner = new DbRunner();

        $schema = 'CREATE TABLE test (
            id INTEGER PRIMARY KEY,
            name BLOB
        );

        INSERT INTO test (name) VALUES (randomblob(1000000));';

        $query = 'SELECT * FROM test;';

        $firstResult = $dbrunner->runQuery($schema, $query);
        $secondResult = $firstResult;

        // check if it always ran schema
        // for an uncached case, it should take a lot of time
        // for a cached case, it should be fast (~3s instead of ~6s)
        for ($i = 0; $i < 50; ++$i) {
            $secondResult = $dbrunner->runQuery($schema, $query);
        }

        self::assertEquals($firstResult, $secondResult);
    }
}
