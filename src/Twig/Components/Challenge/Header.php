<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Header
{
    public User $user;
    public Question $question;
    public int $limit;

    public function __construct(
        private readonly SolutionEventRepository $solutionEventRepository,
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
}

enum SolveState: string implements TranslatableInterface
{
    case Solved = 'solved';
    case Failed = 'failed';
    case NotSolved = 'not-solved';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans("challenge.solve-state.{$this->value}", locale: $locale);
    }
}
