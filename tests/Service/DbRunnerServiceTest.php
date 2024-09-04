<?php

declare(strict_types=1);

namespace App\Tests\Service;

use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DbRunnerServiceTest extends TestCase
{
    public static function formatSqlProvider(): array
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

    #[DataProvider('formatSqlProvider')]
    public function testFormatSql(string $input, string $expect): void
    {
        $formatter = new SqlFormatter(new NullHighlighter());

        $this->assertEquals($expect, $formatter->compress($input));
    }
}
