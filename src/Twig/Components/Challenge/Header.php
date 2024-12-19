<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\PassRate;
use App\Entity\Question;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use App\Service\PassRateService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Header
{
    use DefaultActionTrait;

    #[LiveProp]
    public User $user;

    #[LiveProp]
    public Question $question;

    public function __construct(
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly QuestionRepository $questionRepository,
        private readonly PassRateService $passRateService,
    ) {}

    public function getSolveState(): SolveState
    {
        return match ($this->solutionEventRepository->getSolveState($this->question, $this->user)) {
            SolutionEventStatus::Passed => SolveState::Solved,
            SolutionEventStatus::Failed => SolveState::Failed,
            default => SolveState::NotSolved,
        };
    }

    public function getNextPage(): ?int
    {
        return $this->questionRepository->getNextPage($this->question->getId());
    }

    public function getPreviousPage(): ?int
    {
        return $this->questionRepository->getPreviousPage($this->question->getId());
    }

    public function getPassRate(): PassRate
    {
        return $this->passRateService->getPassRate($this->question, $this->user->getGroup());
    }

    #[LiveListener('app:challenge-executor:query-created')]
    public function onQueryUpdated(): void
    {
        // Update "Solve State" and "Pass Rate" after a new query is created.
    }
}
