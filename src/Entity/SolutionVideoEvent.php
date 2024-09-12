<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\WithUlidCreatedAt;
use App\Repository\SolutionVideoEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: SolutionVideoEventRepository::class)]
class SolutionVideoEvent
{
    use WithUlidCreatedAt;

    #[ORM\Id]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(inversedBy: 'solutionVideoEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $opener = null;

    #[ORM\ManyToOne(inversedBy: 'solutionVideoEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function setId(Ulid $id): static
    {
        $this->id = $id;

        return $this;
    }

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
