<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EmailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: EmailRepository::class)]
class Email
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4096)]
    #[NotBlank]
    private string $subject = '';

    #[ORM\Column(type: Types::TEXT)]
    #[NotBlank]
    private string $content = '';

    /**
     * @var Collection<int, EmailDeliveryEvent>
     */
    #[ORM\OneToMany(targetEntity: EmailDeliveryEvent::class, mappedBy: 'email')]
    private Collection $emailDeliveryEvents;

    #[ORM\Column(enumType: EmailKind::class)]
    private EmailKind $kind = EmailKind::Transactional;

    public function __construct()
    {
        $this->emailDeliveryEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getSubject();
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection<int, EmailDeliveryEvent>
     */
    public function getEmailDeliveryEvents(): Collection
    {
        return $this->emailDeliveryEvents;
    }

    public function addEmailDeliveryEvent(EmailDeliveryEvent $emailDeliveryEvent): static
    {
        if (!$this->emailDeliveryEvents->contains($emailDeliveryEvent)) {
            $this->emailDeliveryEvents->add($emailDeliveryEvent);
            $emailDeliveryEvent->setEmail($this);
        }

        return $this;
    }

    public function removeEmailDeliveryEvent(EmailDeliveryEvent $emailDeliveryEvent): static
    {
        if ($this->emailDeliveryEvents->removeElement($emailDeliveryEvent)) {
            // set the owning side to null (unless already changed)
            if ($emailDeliveryEvent->getEmail() === $this) {
                $emailDeliveryEvent->setEmail(new self());
            }
        }

        return $this;
    }

    public function getKind(): EmailKind
    {
        return $this->kind;
    }

    public function setKind(EmailKind $kind): static
    {
        $this->kind = $kind;

        return $this;
    }
}
