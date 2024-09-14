<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\WithUlid;
use App\Repository\SolutionVideoEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SolutionVideoEventRepository::class)]
class SolutionVideoEvent
{
    use WithUlid;

    #[ORM\ManyToOne(inversedBy: 'solutionVideoEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $opener = null;

    #[ORM\ManyToOne(inversedBy: 'solutionVideoEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    public function getOpener(): ?User
    {
        return $this->opener;
    }

    public function setOpener(?User $opener): static
    {
        $this->opener = $opener;

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
}
