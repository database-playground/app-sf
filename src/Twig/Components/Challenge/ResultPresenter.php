<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\User;
use App\Service\QuestionDbRunnerService;
use App\Twig\Components\Challenge\Payload\ErrorProperty;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ResultPresenter
{
    use DefaultActionTrait;

    /**
     * The available tabs.
     *
     * @var string[]
     */
    public array $tabs = [
        'result', 'answer', 'diff', 'events',
    ];

    /**
     * @var Question $question the question to present the answer
     */
    #[LiveProp]
    public Question $question;

    /**
     * @var User $user the user who is viewing the result,
     *           currently useful for checking the previously-submitted answer
     */
    #[LiveProp]
    public User $user;

    /**
     * @var string $currentTab the current active tab
     */
    #[LiveProp(writable: true)]
    public string $currentTab = 'result';

    /**
     * @var Payload|null $userResult the result of the user's query
     */
    #[LiveProp(updateFromParent: true)]
    public ?Payload $userResult;

    public function __construct(
        private readonly QuestionDbRunnerService $questionDbRunnerService,
    ) {
    }

    /**
     * Get the wrapped payload of the answer.
     */
    public function getAnswerPayload(): Payload
    {
        try {
            $answer = $this->questionDbRunnerService->getAnswerResult($this->question);
        } catch (\Throwable $e) {
            return Payload::error(ErrorProperty::fromCode(500), $e->getMessage());
        }

        return Payload::result($answer, answer: true);
    }
}
