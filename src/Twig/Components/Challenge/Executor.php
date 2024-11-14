<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use App\Service\DbRunnerComparer;
use App\Service\QuestionDbRunnerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Executor
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly QuestionDbRunnerService $questionDbRunnerService,
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[LiveProp]
    public Question $question;

    #[LiveProp]
    public User $user;

    public function getPreviousQuery(): string
    {
        $latestQuery = $this->solutionEventRepository
            ->getLatestQuery($this->question, $this->user);

        return $latestQuery?->getQuery() ?? '';
    }

    #[LiveAction]
    public function createNewQuery(
        #[LiveArg] string $query,
    ): void {
        if ('' === $query) {
            return;
        }

        $solutionEvent = (new SolutionEvent())
            ->setQuestion($this->question)
            ->setSubmitter($this->user)
            ->setQuery($query);

        try {
            $answer = $this->questionDbRunnerService->getAnswerResult($this->question);
            $result = $this->questionDbRunnerService->getQueryResult($this->question, $query);

            $compareResult = DbRunnerComparer::compare($answer, $result);
            $solutionEvent = $solutionEvent->setStatus(
                $compareResult->correct()
                    ? SolutionEventStatus::Passed
                    : SolutionEventStatus::Failed
            );
        } catch (\Throwable) {
            $solutionEvent = $solutionEvent->setStatus(SolutionEventStatus::Failed);
        }

        $this->entityManager->persist($solutionEvent);
        $this->entityManager->flush();

        $this->emit('app:challenge-executor:query-created', [
            'query' => $query,
        ]);
    }
}
