<?php

declare(strict_types=1);

namespace App\Entity\SqlRunnerDto;

class SqlRunnerResult
{
    /**
     * @var list<string> the columns of the result
     */
    private array $columns;

    /**
     * @var list<list<string>> the rows of the result
     */
    private array $rows;

    /**
     * @return list<string> the columns of the result
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param list<string> $columns the columns of the result
     *
     * @return $this
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return list<list<string>> the rows of the result
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param list<list<string>> $rows the rows of the result
     *
     * @return $this
     */
    public function setRows(array $rows): self
    {
        $this->rows = $rows;

        return $this;
    }
}
