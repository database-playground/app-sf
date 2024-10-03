<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Entity\Question;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Card
{
    public Question $question;

    /**
     * Get the pass rate level of the question.
     *
     * Low: 0% - 40%
     * Medium: 41 – 70%
     * High: 71% - 100%
     */
    public function getPassRateLevel(): string
    {
        $passRate = $this->question->getPassRate();

        return match (true) {
            $passRate <= 40 => 'low',
            $passRate <= 70 => 'medium',
            default => 'high',
        };
    }
}
