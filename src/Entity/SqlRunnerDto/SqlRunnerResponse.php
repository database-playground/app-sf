<?php

declare(strict_types=1);

namespace App\Entity\SqlRunnerDto;

/**
 * The response from the SQL runner service.
 */
class SqlRunnerResponse
{
    /**
     * @var bool whether the query was successful
     */
    private bool $success;

    /**
     * @var null|SqlRunnerResult The data returned by the query.
     *                           Only available if the query was successful.
     */
    private ?SqlRunnerResult $data;

    /**
     * @var null|string The error message returned by the query.
     *                  Only available if the query was not successful.
     */
    private ?string $message;

    /**
     * @var null|string The error code returned by the query.
     *                  Only available if the query was not successful.
     */
    private ?string $code;

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function getData(): ?SqlRunnerResult
    {
        return $this->data;
    }

    public function setData(?SqlRunnerResult $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
