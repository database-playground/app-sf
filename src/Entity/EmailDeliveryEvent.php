<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EmailDeliveryEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

#[ORM\Entity(repositoryClass: EmailDeliveryEventRepository::class)]
class EmailDeliveryEvent extends BaseEvent
{
    #[ORM\ManyToOne(inversedBy: 'emailDeliveryEvents')]
    private ?User $toUser = null;

    #[ORM\Column(type: Types::STRING, length: 512)]
    #[EmailConstraint]
    private string $toAddress = '';

    #[ORM\ManyToOne(inversedBy: 'emailDeliveryEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private Email $email;

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

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): static
    {
        $this->email = $email;

        return $this;
    }
}
