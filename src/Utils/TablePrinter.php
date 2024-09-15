<?php

declare(strict_types=1);

namespace App\Utils;

class TablePrinter
{
    /**
     * Turn a table into a string.
     *
     * @param array<array<string, mixed>> $table The table to turn into a string
     *
     * @return string The string representation of the table
     */
    public static function toStringTable(array $table): string
    {
        $result = '';

        if (0 === \count($table)) {
            return $result;
        }

        $columns = array_keys($table[0]);

        // calculate the max width of each column
        $columnWidths = array_map(fn ($column) => \strlen($column), $columns);

        foreach ($table as $row) {
            foreach ($columns as $i => $column) {
                if (null === $row[$column]) {
                    $row[$column] = 'NULL';
                }

                $columnWidths[$i] = max($columnWidths[$i], \strlen(self::mixedToString($row[$column])));
            }
        }

        // print the header

        $header = '';

        foreach ($columns as $i => $column) {
            $header .= str_pad($column, $columnWidths[$i] + 2);
        }

        $result .= trim($header)."\n";

        // print the rows
        foreach ($table as $row) {
            $line = '';

            foreach ($columns as $i => $column) {
                if (null === $row[$column]) {
                    $row[$column] = 'NULL';
                }

                $line .= str_pad(self::mixedToString($row[$column]), $columnWidths[$i] + 2);
            }

            $result .= trim($line)."\n";
        }

        return $result;
    }

    public static function mixedToString(mixed $value): string
    {
        // make sure if $value is the primitive type
        if (\is_string($value) || \is_int($value) || \is_float($value) || \is_bool($value)) {
            return (string) $value;
        }

        // if $value == null, it shows as 'NULL'
        if (null === $value) {
            return 'NULL';
        }

        // for other cases, we export it
        $exported = var_export($value, true);

        // remove the new line character and the leading space
        return str_replace(["\n", '  ', ',)'], ['', '', ')'], $exported);
    }
}
