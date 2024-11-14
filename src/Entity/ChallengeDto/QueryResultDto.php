<?php

declare(strict_types=1);

namespace App\Entity\ChallengeDto;

use App\Service\DbRunner;

/**
 * The typed wrapper for the result of a query from {@link DbRunner}.
 */
class QueryResultDto
{
    /**
     * @var array<array<int, string>> the result of the user's query
     */
    private array $result;

    /**
     * @return array<array<int, string>> the result of the user's query
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array<array<int, string>> $result the result of the user's query
     */
    public function setResult(array $result): self
    {
        $this->result = $result;

        return $this;
    }
}
