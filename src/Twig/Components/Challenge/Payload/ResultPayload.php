<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Payload;

use Symfony\UX\LiveComponent\Attribute\LiveProp;

class ResultPayload
{
    /**
     * The result of the query.
     *
     * @var array<string, array<string, mixed>> $queryResult
     */
    #[LiveProp]
    public array $queryResult;

    /**
     * Indicate if this is same as the answer.
     */
    #[LiveProp]
    public bool $same;

    /**
     * Indicate if this is the answer.
     */
    #[LiveProp]
    public bool $answer;
}
