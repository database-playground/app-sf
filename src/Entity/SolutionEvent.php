<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\WithUlid;
use App\Repository\SolutionEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SolutionEventRepository::class)]
class SolutionEvent
{
    use WithUlid;

    #[ORM\ManyToOne(inversedBy: 'solutionEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitter = null;

    #[ORM\ManyToOne(inversedBy: 'solutionEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(enumType: SolutionEventStatus::class)]
    private ?SolutionEventStatus $status = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $query = null;

    public function getSubmitter(): ?User
    {
        return $this->submitter;
    }

    public function setSubmitter(?User $submitter): static
    {
        $this->submitter = $submitter;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getStatus(): SolutionEventStatus
    {
        return $this->status ?? SolutionEventStatus::Unspecified;
    }

    public function setStatus(SolutionEventStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getQuery(): string
    {
        return (string) $this->query;
    }

    public function setQuery(string $query): static
    {
        $this->query = $query;

        return $this;
    }
}
