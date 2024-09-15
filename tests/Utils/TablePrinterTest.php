<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\TablePrinter;
use PHPUnit\Framework\TestCase;

class TablePrinterTest extends TestCase
{
    public function testToStringTable(): void
    {
        $table = [
            ['name' => 'Alice', 'age' => 21],
            ['name' => 'Bob', 'age' => 22],
            ['name' => 'Charlie', 'age' => 23],
        ];

        $expected = <<<EOT
            name     age
            Alice    21
            Bob      22
            Charlie  23

            EOT;

        $this->assertEquals($expected, TablePrinter::toStringTable($table));
    }

    public function testToNullTable(): void
    {
        $table = [
            ['name' => 'Alice', 'age' => 21],
            ['name' => 'Bob', 'age' => null],
            ['name' => 'Charlie', 'age' => 23],
        ];

        $expected = <<<EOT
            name     age
            Alice    21
            Bob      NULL
            Charlie  23

            EOT;

        $this->assertEquals($expected, TablePrinter::toStringTable($table));
    }

    public function testEmptyTable(): void
    {
        $table = [];

        $this->assertEquals('', TablePrinter::toStringTable($table));
    }

    public function testEmptyRow(): void
    {
        $table = [
            ['name' => 'Alice', 'age' => 21],
            [],
            ['name' => 'Charlie', 'age' => 23],
        ];

        $expected = <<<EOT
            name     age
            Alice    21
            NULL     NULL
            Charlie  23

            EOT;

        $this->assertEquals($expected, TablePrinter::toStringTable($table));
    }

    public function testEmptyColumn(): void
    {
        $table = [
            ['name' => 'Alice', 'age' => 21],
            ['name' => 'Bob'],
            ['name' => 'Charlie', 'age' => 23],
        ];

        $expected = <<<EOT
            name     age
            Alice    21
            Bob      NULL
            Charlie  23

            EOT;

        $this->assertEquals($expected, TablePrinter::toStringTable($table));
    }

    public function testNotPrimitive(): void
    {
        $table = [
            ['name' => 'Alice', 'age' => 21],
            ['name' => 'Bob', 'age' => ['foo' => 'bar']],
            ['name' => 'Charlie', 'age' => 23],
        ];

        $expected = <<<EOT
            name     age
            Alice    21
            Bob      array ('foo' => 'bar')
            Charlie  23

            EOT;

        $this->assertEquals($expected, TablePrinter::toStringTable($table));
    }

    public function testIntegerKey(): void
    {
        $table = [
            ['name' => 'Alice', 1 => 21],
            ['name' => 'Bob', 1 => 'foo'],
            ['name' => 'Charlie', 1 => 23],
        ];

        $expected = <<<EOT
            name     1
            Alice    21
            Bob      foo
            Charlie  23

            EOT;

        $this->assertEquals($expected, TablePrinter::toStringTable($table));
    }
}
