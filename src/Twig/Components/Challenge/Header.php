<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Header
{
    public User $user;
    public Question $question;
    public int $limit;

    public function __construct(
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly QuestionRepository $questionRepository,
    ) {
    }

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
}
