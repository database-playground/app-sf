<?php

declare(strict_types=1);

namespace App\Entity;

class SqlExecuteRequest
{
    protected string $schema;
    protected string $query;

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }
}
