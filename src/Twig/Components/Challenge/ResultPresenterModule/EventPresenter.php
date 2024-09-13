<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Entity\Question;
use App\Entity\SolutionEvent;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class EventPresenter
{
    public Question $question;
    public User $user;

    public function __construct(
        private readonly SolutionEventRepository $solutionEventRepository,
    ) {
    }

    /**
     * @return SolutionEvent[]
     */
    public function getEvents(): array
    {
        return $this->solutionEventRepository->findUserQuestionEvents(
            question: $this->question,
            user: $this->user,
        );
    }
}
