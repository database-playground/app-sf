<?php

declare(strict_types=1);

namespace App\Entity\EmailDto;

class SentEmailDto extends EmailDto
{
    private \DateTimeInterface $sentAt;

    public function getSentAt(): \DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }
}
