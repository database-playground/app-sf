<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use Symfony\Component\Validator\Constraints as Assert;

readonly class QuestionDto extends Importable
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $schemaId,

        #[Assert\NotBlank]
        public string $type,

        #[Assert\NotBlank]
        public QuestionDifficulty $difficulty,

        #[Assert\NotBlank]
        public string $title,

        public ?string $description,

        public string $answer,

        public ?string $solutionVideo,
    ) {
    }

    public static function fromEntity(Question $question): self
    {
        return new self(
            schemaId: $question->getSchema()?->getId(),
            type: $question->getType(),
            difficulty: $question->getDifficulty(),
            title: $question->getTitle(),
            description: $question->getDescription(),
            answer: $question->getAnswer(),
            solutionVideo: $question->getSolutionVideo(),
        );
    }

    public static function fromJsonObjectRaw(object $json): self
    {
        return new self(
            schemaId: $json->schema_id,
            type: $json->type,
            difficulty: QuestionDifficulty::fromString($json->difficulty),
            title: $json->title,
            description: $json->description,
            answer: $json->answer,
            solutionVideo: $json->solutionVideo,
        );
    }
}