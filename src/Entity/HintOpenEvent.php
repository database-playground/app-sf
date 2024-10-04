<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HintOpenEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HintOpenEventRepository::class)]
class HintOpenEvent extends BaseEvent
{
    #[ORM\ManyToOne(inversedBy: 'hintOpenEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private User $opener;

    #[ORM\ManyToOne(inversedBy: 'hintOpenEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    #[ORM\Column(type: Types::TEXT)]
    private string $response;

    #[ORM\Column(type: Types::TEXT)]
    private string $query;

    public function getOpener(): User
    {
        return $this->opener;
    }

    public function setOpener(User $opener): static
    {
        $this->opener = $opener;

        return $this;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): static
    {
        $this->response = $response;

        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): static
    {
        $this->question = $question;

        return $this;
    }
}
