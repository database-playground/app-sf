<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Repository\SchemaRepository;

class QuestionDto
{
    private string $schemaId;
    private string $type;
    private QuestionDifficulty $difficulty;
    private string $title;
    private ?string $description = null;
    private string $answer;
    private ?string $solutionVideo = null;

    public function getSchemaId(): string
    {
        return $this->schemaId;
    }

    public function setSchemaId(string $schemaId): self
    {
        $this->schemaId = $schemaId;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDifficulty(): QuestionDifficulty
    {
        return $this->difficulty;
    }

    public function setDifficulty(QuestionDifficulty $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getSolutionVideo(): ?string
    {
        return $this->solutionVideo;
    }

    public function setSolutionVideo(?string $solutionVideo): self
    {
        $this->solutionVideo = $solutionVideo;

        return $this;
    }

    public static function fromEntity(Question $question): self
    {
        return (new self())
            ->setSchemaId($question->getSchema()->getId())
            ->setType($question->getType())
            ->setDifficulty($question->getDifficulty())
            ->setTitle($question->getTitle())
            ->setDescription($question->getDescription())
            ->setAnswer($question->getAnswer())
            ->setSolutionVideo($question->getSolutionVideo());
    }

    public function toEntity(SchemaRepository $schemaRepository): Question
    {
        $schema = $schemaRepository->find($this->schemaId);
        if (null === $schema) {
            throw new \RuntimeException("Schema $this->schemaId not found");
        }

        return (new Question())
            ->setSchema($schema)
            ->setType($this->type)
            ->setDifficulty($this->difficulty)
            ->setTitle($this->title)
            ->setDescription($this->description)
            ->setAnswer($this->answer)
            ->setSolutionVideo($this->solutionVideo);
    }
}
