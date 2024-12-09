<?php

declare(strict_types=1);

namespace App\Entity\EmailDto;

use App\Entity\EmailKind;
use App\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailDto
{
    private Address $toAddress;
    private string $subject;
    private EmailKind $kind;
    private string $text;
    private string $html;

    public static function fromUser(User $user): self
    {
        return (new self())
            ->setToAddress(new Address(
                address: $user->getEmail(),
                name: $user->getName() ?? $user->getEmail(),
            ));
    }

    public function getToAddress(): Address
    {
        return $this->toAddress;
    }

    public function setToAddress(Address $toAddress): self
    {
        $this->toAddress = $toAddress;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getKind(): EmailKind
    {
        return $this->kind;
    }

    public function setKind(EmailKind $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    public function toEmail(): Email
    {
        $email = (new Email())
            ->to($this->getToAddress())
            ->subject($this->getSubject())
            ->text($this->getText())
            ->html($this->getHtml());

        $headers = $this->getKind()->addToEmailHeader($email->getHeaders());

        return $email->setHeaders($headers);
    }
}
