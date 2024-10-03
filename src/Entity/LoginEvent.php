<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LoginEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoginEventRepository::class)]
class LoginEvent extends BaseEvent
{
    #[ORM\ManyToOne(inversedBy: 'loginEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $account = null;

    public function getAccount(): ?User
    {
        return $this->account;
    }

    public function setAccount(?User $account): static
    {
        $this->account = $account;

        return $this;
    }
}
