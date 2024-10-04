<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Entity\Question;
use App\Entity\User;
use App\Service\PassRateService;
use App\Service\Types\PassRate;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Card
{
    public Question $question;
    public User $currentUser;

    public function __construct(
        private readonly PassRateService $passRateService,
    ) {
    }

    /**
     * Get the pass rate of the question.
     */
    public function getPassRate(): PassRate
    {
        return $this->passRateService->getPassRate($this->question, $this->currentUser->getGroup());
    }
}
