<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EmailEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: EmailEventRepository::class)]
class EmailEvent extends BaseEvent
{
    #[ORM\ManyToOne(inversedBy: 'emailEvents')]
    private ?User $toUser = null;

    #[ORM\Column(type: Types::STRING, length: 512)]
    #[Email]
    private string $toAddress = '';

    #[ORM\Column(type: Types::STRING, length: 4096)]
    #[NotBlank]
    private string $subject = '';

    #[ORM\Column(type: Types::TEXT)]
    #[NotBlank]
    private string $content = '';

    public function getToUser(): ?User
    {
        return $this->toUser;
    }

    public function setToUser(?User $toUser): static
    {
        $this->toUser = $toUser;

        return $this;
    }

    public function getToAddress(): ?string
    {
        return $this->toAddress;
    }

    public function setToAddress(string $toAddress): static
    {
        $this->toAddress = $toAddress;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
