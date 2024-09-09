<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Entity\Question;
use App\Entity\SolutionEvent;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class EventPresenter
{
    public Question $question;
    protected User $user;

    public function __construct(
        protected SolutionEventRepository $solutionEventRepository,
        protected Security $security,
    ) {
        $user = $this->security->getUser();
        \assert($user instanceof User);

        $this->user = $user;
    }

    /**
     * @return array<SolutionEvent>
     */
    public function getEvents(): array
    {
        return $this->solutionEventRepository->listSolvedEvents(
            question: $this->question,
            user: $this->user,
        );
    }
}
