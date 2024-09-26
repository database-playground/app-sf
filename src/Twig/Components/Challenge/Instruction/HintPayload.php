<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Instruction;

class HintPayload
{
    private ?string $hint = null;
    private ?string $error = null;
    private bool $loading = false;

    public function getHint(): ?string
    {
        return $this->hint;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getLoading(): bool
    {
        return $this->loading;
    }

    public function setHint(?string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function setLoading(bool $loading): self
    {
        $this->loading = $loading;

        return $this;
    }

    public static function fromHint(string $hint): self
    {
        $payload = new self();
        $payload->setHint($hint);

        return $payload;
    }

    public static function fromError(string $error): self
    {
        $payload = new self();
        $payload->setError($error);

        return $payload;
    }

    public static function newLoading(): self
    {
        $payload = new self();
        $payload->setLoading(true);

        return $payload;
    }
}
