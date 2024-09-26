<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Instruction;

class HintPayload
{
    private string $hint;
    private string $error;

    public function getHint(): string
    {
        return $this->hint;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function setHint(string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }
}
