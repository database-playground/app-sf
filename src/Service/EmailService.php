<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EmailDto\EmailDto;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final readonly class EmailService
{
    private Address $fromAddress;
    private Address $testAddress;

    /**
     * @param int<1, max> $chunkLimit
     */
    public function __construct(
        private MailerInterface $mailer,
        private string $serverMail,
        private string $serverMailForTest,
        private int $chunkLimit,
    ) {
        $this->fromAddress = new Address(
            address: $this->serverMail,
            name: '資料庫練功房'
        );

        $this->testAddress = new Address(
            address: $this->serverMailForTest,
            name: '資料庫練功房 - 測試信箱'
        );
    }

    /**
     * Send an email with the given {@link EmailDto}.
     *
     * @param EmailDto $emailDto the email to send
     *
     * @throws TransportExceptionInterface   if the email cannot be sent
     * @throws \DateMalformedStringException if the date is malformed
     */
    public function send(EmailDto $emailDto): void
    {
        $recipients = $emailDto->getBcc();

        if (\count($recipients) > 0) {
            $sendAt = new \DateTimeImmutable();
            $chunks = array_chunk($recipients, $this->chunkLimit);
            foreach ($chunks as $chunk) {
                $email = $emailDto
                    ->toEmail()
                    ->from($this->fromAddress)
                    ->bcc(...$chunk)
                    ->date($sendAt)
                ;
                $this->mailer->send($email);

                $sendAt = $sendAt->modify('+3 seconds');
            }
        } else {
            $email = $emailDto->toEmail()->from($this->fromAddress);
            $this->mailer->send($email);
        }
    }

    /**
     * Send an email with the given {@link EmailDto} to the test email address.
     */
    public function sendToTest(EmailDto $emailDto): void
    {
        $emailDtoCloned = clone $emailDto;
        $emailDtoCloned->setToAddress($this->testAddress)->setBcc([]);
        $email = $emailDtoCloned->toEmail()->from($this->fromAddress);
        $this->mailer->send($email);
    }
}
