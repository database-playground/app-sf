<?php

declare(strict_types=1);

namespace App\Entity\ChallengeDto;

use App\Entity\SqlRunnerDto\SqlRunnerResult;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * A DTO for the result of a query that may contain the error from DbRunner.
 *
 * If there is no error, the errorCode will be 0.
 */
class FallableQueryResultDto
{
    public ?SqlRunnerResult $result = null;
    public ?TranslatableMessage $errorMessage = null;

    public function getResult(): ?SqlRunnerResult
    {
        return $this->result;
    }

    public function setResult(?SqlRunnerResult $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getErrorMessage(): ?TranslatableMessage
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?TranslatableMessage $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }
}
