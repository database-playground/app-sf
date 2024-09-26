<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Twig\Components\Challenge\Payload\ErrorProperty;

class Payload
{
    private ?Payload\ResultPayload $result = null;
    private ?Payload\ErrorPayload $error = null;

    public function getResult(): ?Payload\ResultPayload
    {
        return $this->result;
    }

    public function getError(): ?Payload\ErrorPayload
    {
        return $this->error;
    }

    public function setResult(?Payload\ResultPayload $result): void
    {
        $this->result = $result;
    }

    public function setError(?Payload\ErrorPayload $error): void
    {
        $this->error = $error;
    }

    /**
     * A convenient method to create a result payload.
     *
     * @param array<array<string, mixed>> $queryResult the result of the query
     * @param bool                        $same        whether the result is the same as the answer
     * @param bool                        $answer      whether the result is the answer
     *
     * @return self the payload
     */
    public static function fromResult(array $queryResult, bool $same = false, bool $answer = false): self
    {
        $payload = new self();
        $payload->setResult(
            (new Payload\ResultPayload())
                ->setQueryResult($queryResult)
                ->setSame($same)
                ->setAnswer($answer)
        );

        return $payload;
    }

    /**
     * A convenient method to create an error payload.
     *
     * @param ErrorProperty $property the error property
     * @param string        $message  the error message
     *
     * @return self the payload
     */
    public static function fromError(ErrorProperty $property, string $message): self
    {
        $payload = new self();
        $payload->setError(
            (new Payload\ErrorPayload())
                ->setProperty($property)
                ->setMessage($message)
        );

        return $payload;
    }

    /**
     * A convenient method to create an error (with code).
     *
     * @param int    $code    the error code
     * @param string $message the error message
     *
     * @return self the payload
     */
    public static function fromErrorWithCode(int $code, string $message): self
    {
        return self::fromError(ErrorProperty::fromCode($code), $message);
    }
}
