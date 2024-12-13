<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EmailDto\EmailDto;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

readonly class EmailService
{
    private Address $fromAddress;

    public function __construct(
        private MailerInterface $mailer,
        private string $serverMail,
    ) {
        $this->fromAddress = new Address(
            address: $this->serverMail,
            name: '資料庫練功房'
        );
    }

    /**
     * Send an email with the given {@link EmailDto}.
     *
     * @param EmailDto $emailDto the email to send
     *
     * @throws TransportExceptionInterface
     */
    public function send(EmailDto $emailDto): Email
    {
        $email = $emailDto->toEmail()->from($this->fromAddress);

        $this->mailer->send($email);

        return $email;
    }
}
