<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Entity\SolutionVideoEvent;
use App\Entity\User;
use App\Repository\SolutionVideoEventRepository;
use App\Service\PointCalculationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly SolutionVideoEventRepository $solutionVideoEventRepository,
    ) {
    }

    public function getCost(): int
    {
        return match ($this->question->getDifficulty()) {
            QuestionDifficulty::Easy => PointCalculationService::$solutionVideoEventEasy,
            QuestionDifficulty::Medium => PointCalculationService::$solutionVideoEventMedium,
            QuestionDifficulty::Hard => PointCalculationService::$solutionVideoEventHard,
            default => 0,
        };
    }

    public function getUnlocked(): bool
    {
        return $this->solutionVideoEventRepository->hasTriggered($this->user, $this->question);
    }

    /**
     * Get the solution video.
     *
     * It writes the solution video event to the database,
     * then emits the `challenge:solution-video:open` event
     * for the solution video.
     *
     * Our Stimulus controller handles the `window.open()` call
     * for the video.
     */
    #[LiveAction]
    public function openSolutionVideo(): void
    {
        $event = (new SolutionVideoEvent())
            ->setQuestion($this->question)
            ->setOpener($this->user);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->dispatchBrowserEvent('challenge:solution-video:open', [
            'solutionVideo' => $this->question->getSolutionVideo(),
        ]);
    }
}
