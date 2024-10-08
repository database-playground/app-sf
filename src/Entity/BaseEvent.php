<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class BaseEvent
{
    #[ORM\Id]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    protected ?Ulid $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    /**
     * Get the created at date and time of the solution event.
     *
     * @return \DateTimeImmutable The parsed DateTime object
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function updateCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
