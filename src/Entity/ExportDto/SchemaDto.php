<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

use App\Entity\Schema;

class SchemaDto
{
    private string $id;
    private ?string $picture = null;
    private ?string $description = null;
    private string $schema;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function setSchema(string $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    public static function fromEntity(Schema $schema): self
    {
        return (new self())
            ->setId($schema->getId())
            ->setPicture($schema->getPicture())
            ->setDescription($schema->getDescription())
            ->setSchema($schema->getSchema());
    }

    public function toEntity(): Schema
    {
        return (new Schema())
            ->setId($this->id)
            ->setPicture($this->picture)
            ->setDescription($this->description)
            ->setSchema($this->schema);
    }
}
