<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Entity\Question;
use App\Entity\SolutionEvent;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class EventPresenter
{
    use DefaultActionTrait;
    use Pagination;

    #[LiveProp]
    public Question $question;

    #[LiveProp]
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
        return \array_slice($this->getData(), 0, self::$LIMIT);
    }

    /**
     * Get the data that can be paginated.
     *
     * It includes `[0, self::$LIMIT+1]` elements, where the last
     * element is used to determine if there are more pages.
     *
     * @return SolutionEvent[]
     */
    protected function getData(): array
    {
        return $this->solutionEventRepository->findUserQuestionEvents(
            question: $this->question,
            user: $this->user,
            limit: self::$LIMIT + 1 /* more? */,
            offset: ($this->page - 1) * self::$LIMIT,
        );
    }
}
