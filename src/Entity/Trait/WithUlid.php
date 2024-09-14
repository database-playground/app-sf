<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

trait WithUlid
{
    #[ORM\Id]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private ?Ulid $id = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    /**
     * Get the created at date and time of the solution event.
     *
     * @return ?\DateTimeImmutable The parsed DateTime object
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->id?->getDateTime();
    }
}
