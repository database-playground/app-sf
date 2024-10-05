<?php

declare(strict_types=1);

namespace App\Entity\ExportDto;

class ExportedDataDto
{
    /**
     * @var array<string, SchemaDto>
     */
    private array $schemas;

    /**
     * @var list<QuestionDto>
     */
    private array $questions;

    /**
     * @return array<string, SchemaDto>
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    /**
     * @param array<string, SchemaDto> $schemas
     */
    public function setSchemas(array $schemas): self
    {
        $this->schemas = $schemas;

        return $this;
    }

    /**
     * @return list<QuestionDto>
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    /**
     * @param list<QuestionDto> $questions
     */
    public function setQuestions(array $questions): self
    {
        $this->questions = $questions;

        return $this;
    }
}
