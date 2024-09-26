<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Payload;

class ResultPayload
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $queryResult;
    private bool $same;
    private bool $answer;

    /**
     * Get the result of the query.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getQueryResult(): array
    {
        return $this->queryResult;
    }

    /**
     * Get if this is same as the answer.
     */
    public function isSame(): bool
    {
        return $this->same;
    }

    /**
     * Get if this is the answer.
     */
    public function isAnswer(): bool
    {
        return $this->answer;
    }

    /**
     * Set the result of the query.
     *
     * @param array<string, array<string, mixed>> $queryResult
     */
    public function setQueryResult(array $queryResult): self
    {
        $this->queryResult = $queryResult;

        return $this;
    }

    /**
     * Set if this is same as the answer.
     */
    public function setSame(bool $same): self
    {
        $this->same = $same;

        return $this;
    }

    /**
     * Set if this is the answer.
     */
    public function setAnswer(bool $answer): self
    {
        $this->answer = $answer;

        return $this;
    }
}
