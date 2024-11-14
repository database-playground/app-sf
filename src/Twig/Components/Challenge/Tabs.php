<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\User;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Tabs
{
    use DefaultActionTrait;

    /**
     * The available tabs.
     *
     * @var string[]
     */
    public array $tabs = [
        'result', 'answer', 'events',
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
}
