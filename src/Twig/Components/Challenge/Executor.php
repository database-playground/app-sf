<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Service\QuestionDbRunnerService;
use Psr\Cache\InvalidArgumentException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Executor
{
    use DefaultActionTrait;

    public function __construct(
        protected QuestionDbRunnerService $questionDbRunnerService,
    ) {
    }

    #[LiveProp]
    public Question $question;

    /**
     * @var string the query to execute
     */
    #[LiveProp(writable: true)]
    public string $query = '';

    /**
     * @var bool whether the query is pending
     */
    public bool $pending = false;

    /**
     * @var array<array<string, mixed>> the result of the query
     */
    #[LiveProp]
    public array $result = [];

    /**
     * @throws InvalidArgumentException
     */
    #[LiveAction]
    public function execute(): void
    {
        $this->pending = true;
        $result = $this->questionDbRunnerService->getQueryResult($this->question, $this->query);

        $this->result = $result;
        $this->pending = false;
    }
}
