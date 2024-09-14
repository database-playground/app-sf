<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

use App\Entity\Schema;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SchemaDto extends Importable
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

    public static function fromJsonObjectRaw(object $json): self
    {
        return new self(
            id: $json->id,
            picture: $json->picture,
            description: $json->description,
            schema: $json->schema,
        );
    }
}