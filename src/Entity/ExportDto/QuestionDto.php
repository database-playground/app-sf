<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Repository\SchemaRepository;
use Symfony\Component\Validator\Constraints as Assert;

readonly class QuestionDto implements Importable
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

    public function toEntity(SchemaRepository $schemaRepository): Question
    {
        $schema = $schemaRepository->find($this->schemaId);

        return (new Question())
            ->setSchema($schema)
            ->setType($this->type)
            ->setDifficulty($this->difficulty)
            ->setTitle($this->title)
            ->setDescription($this->description)
            ->setAnswer($this->answer)
            ->setSolutionVideo($this->solutionVideo);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromJsonObject(\stdClass $json): self
    {
        $json = clone $json;

        if (!isset($json->schemaId)) {
            throw new \InvalidArgumentException('schemaId is required');
        }
        if (!\is_string($json->schemaId)) {
            throw new \InvalidArgumentException('schemaId must be of type string');
        }

        if (!isset($json->type)) {
            throw new \InvalidArgumentException('type is required');
        }
        if (!\is_string($json->type)) {
            throw new \InvalidArgumentException('type must be of type string');
        }

        if (!isset($json->difficulty)) {
            throw new \InvalidArgumentException('difficulty is required');
        }
        if (!\is_string($json->difficulty)) {
            throw new \InvalidArgumentException('difficulty must be of type string');
        }

        if (!isset($json->title)) {
            throw new \InvalidArgumentException('title is required');
        }
        if (!\is_string($json->title)) {
            throw new \InvalidArgumentException('title must be of type string');
        }

        if (!isset($json->answer)) {
            throw new \InvalidArgumentException('answer is required');
        }
        if (!\is_string($json->answer)) {
            throw new \InvalidArgumentException('answer must be of type string');
        }

        if (!isset($json->description)) {
            $json->description = null;
        }
        if (!\is_string($json->description) && null !== $json->description) {
            throw new \InvalidArgumentException('description must be of type string');
        }

        if (!isset($json->solutionVideo)) {
            $json->solutionVideo = null;
        }
        if (!\is_string($json->solutionVideo) && null !== $json->solutionVideo) {
            throw new \InvalidArgumentException('solutionVideo must be of type string');
        }

        return new self(
            schemaId: $json->schemaId,
            type: $json->type,
            difficulty: QuestionDifficulty::fromString($json->difficulty),
            title: $json->title,
            description: $json->description,
            answer: $json->answer,
            solutionVideo: $json->solutionVideo,
        );
    }
}
