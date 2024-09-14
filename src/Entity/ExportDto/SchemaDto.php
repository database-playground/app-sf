<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

use App\Entity\Schema;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SchemaDto implements Importable
{
    public function __construct(
        #[Assert\NotBlank]
        public string $id,

        public ?string $picture,

        public ?string $description,

        #[Assert\NotBlank]
        public string $schema,
    ) {
    }

    public static function fromEntity(Schema $schema): self
    {
        return new self(
            id: $schema->getId(),
            picture: $schema->getPicture(),
            description: $schema->getDescription(),
            schema: $schema->getSchema(),
        );
    }

    public function toEntity(): Schema
    {
        return (new Schema())
            ->setId($this->id)
            ->setPicture($this->picture)
            ->setDescription($this->description)
            ->setSchema($this->schema);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromJsonObject(object $json): self
    {
        /** @var \stdClass $json */
        $json = clone $json;

        if (!isset($json->id)) {
            throw new \InvalidArgumentException('The id must be set.');
        }
        if (!\is_string($json->id)) {
            throw new \InvalidArgumentException('The id must be a string.');
        }

        if (!isset($json->picture)) {
            $json->picture = null;
        }
        if (!\is_string($json->picture) && null !== $json->picture) {
            throw new \InvalidArgumentException('The picture must be a string.');
        }

        if (!isset($json->description)) {
            $json->description = null;
        }
        if (!\is_string($json->description) && null !== $json->description) {
            throw new \InvalidArgumentException('The description must be a string.');
        }

        if (!isset($json->schema)) {
            throw new \InvalidArgumentException('The schema must be set.');
        }
        if (!\is_string($json->schema)) {
            throw new \InvalidArgumentException('The schema must be a string.');
        }

        return new self(
            id: $json->id,
            picture: $json->picture,
            description: $json->description,
            schema: $json->schema,
        );
    }
}
