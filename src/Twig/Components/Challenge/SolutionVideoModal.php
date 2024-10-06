<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Entity\User;
use App\Repository\SolutionVideoEventRepository;
use App\Service\PointCalculationService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SolutionVideoModal
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public Question $question;

    #[LiveProp]
    public User $user;

    public function __construct(
        private readonly SolutionVideoEventRepository $solutionVideoEventRepository,
    ) {
    }

    public function getCost(): int
    {
        return match ($this->question->getDifficulty()) {
            QuestionDifficulty::Easy => PointCalculationService::solutionVideoEventEasy,
            QuestionDifficulty::Medium => PointCalculationService::solutionVideoEventMedium,
            QuestionDifficulty::Hard => PointCalculationService::solutionVideoEventHard,
            default => 0,
        };
    }

    public function getUnlocked(): bool
    {
        return $this->solutionVideoEventRepository->hasTriggered($this->user, $this->question);
    }
}
