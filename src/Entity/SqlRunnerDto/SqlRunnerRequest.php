<?php

declare(strict_types=1);

namespace App\Entity\SqlRunnerDto;

class SqlRunnerRequest
{
    private string $schema;
    private string $query;

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function setSchema(string $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }
}
