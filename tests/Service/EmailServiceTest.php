<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\EmailDto\EmailDto;
use App\Entity\EmailKind;
use App\Service\EmailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailServiceTest extends TestCase
{
    public function testSendWithoutBcc(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (Email $email): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test2@gmail.com', $email->getTo()[0]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());
            });

        $emailService = new EmailService($mailer, 'test@example.com');
        $emailDto = (new EmailDto())
            ->setSubject('Test')
            ->setToAddress('test2@gmail.com')
            ->setKind(EmailKind::Test)
            ->setText('Test TEXT')
            ->setHtml('Test HTML')
        ;

        $emailService->send($emailDto);
    }

    public function testSendWithBcc(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (Email $email): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test2@gmail.com', $email->getTo()[0]->getAddress());
                self::assertSame('me@pan93.com', $email->getBcc()[0]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());
            });

        $emailService = new EmailService($mailer, 'test@example.com');
        $emailDto = (new EmailDto())
            ->setSubject('Test')
            ->setToAddress('test2@gmail.com')
            ->setBcc([
                new Address('me@pan93.com'),
            ])
            ->setKind(EmailKind::Test)
            ->setText('Test TEXT')
            ->setHtml('Test HTML')
        ;

        $emailService->send($emailDto);
    }

    public function testSendWith29Bcc(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (Email $email): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test@example.com', $email->getTo()[0]->getAddress());
                self::assertCount(29, $email->getBcc());
                self::assertSame('bcc1@example.com', $email->getBcc()[0]->getAddress());
                self::assertSame('bcc29@example.com', $email->getBcc()[28]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());
            });

        $emailService = new EmailService($mailer, 'test@example.com');
        $emailDto = (new EmailDto())
            ->setSubject('Test')
            ->setToAddress('test@example.com')
            ->setBcc(array_map(
                fn (int $i) => new Address("bcc$i@example.com"),
                range(1, 29)
            ))
            ->setKind(EmailKind::Test)
            ->setText('Test TEXT')
            ->setHtml('Test HTML');

        $emailService->send($emailDto);
    }

    public function testSendWith30Bcc(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (Email $email): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test@example.com', $email->getTo()[0]->getAddress());
                self::assertCount(30, $email->getBcc());
                self::assertSame('bcc1@example.com', $email->getBcc()[0]->getAddress());
                self::assertSame('bcc30@example.com', $email->getBcc()[29]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());
            });

        $emailService = new EmailService($mailer, 'test@example.com');
        $emailDto = (new EmailDto())
            ->setSubject('Test')
            ->setToAddress('test@example.com')
            ->setBcc(array_map(
                fn (int $i) => new Address("bcc$i@example.com"),
                range(1, 30)
            ))
            ->setKind(EmailKind::Test)
            ->setText('Test TEXT')
            ->setHtml('Test HTML');

        $emailService->send($emailDto);
    }

    public function testSendWith31Bcc(): void
    {
        $invokedCount = self::exactly(2);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($invokedCount)
            ->method('send')
            ->willReturnCallback(function (Email $email) use (&$invokedCount): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());

                switch ($invokedCount->numberOfInvocations()) {
                    case 1:
                        self::assertCount(30, $email->getBcc());
                        self::assertSame('bcc1@example.com', $email->getBcc()[0]->getAddress());
                        self::assertSame('bcc30@example.com', $email->getBcc()[29]->getAddress());
                        break;
                    case 2:
                        self::assertCount(1, $email->getBcc());
                        self::assertSame('bcc31@example.com', $email->getBcc()[0]->getAddress());
                }
            });

        $emailService = new EmailService($mailer, 'test@example.com');
        $emailDto = (new EmailDto())
            ->setSubject('Test')
            ->setToAddress('test@example.com')
            ->setBcc(array_map(
                fn (int $i) => new Address("bcc$i@example.com"),
                range(1, 31)
            ))
            ->setKind(EmailKind::Test)
            ->setText('Test TEXT')
            ->setHtml('Test HTML');

        $emailService->send($emailDto);
    }

    public function testSendWith61Bcc(): void
    {
        $invokedCount = self::exactly(3);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($invokedCount)
            ->method('send')
            ->willReturnCallback(function (Email $email) use (&$invokedCount): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());

                switch ($invokedCount->numberOfInvocations()) {
                    case 1:
                        self::assertCount(30, $email->getBcc());
                        self::assertSame('bcc1@example.com', $email->getBcc()[0]->getAddress());
                        self::assertSame('bcc30@example.com', $email->getBcc()[29]->getAddress());
                        break;
                    case 2:
                        self::assertCount(30, $email->getBcc());
                        self::assertSame('bcc31@example.com', $email->getBcc()[0]->getAddress());
                        self::assertSame('bcc60@example.com', $email->getBcc()[29]->getAddress());
                        break;
                    case 3:
                        self::assertCount(1, $email->getBcc());
                        self::assertSame('bcc61@example.com', $email->getBcc()[0]->getAddress());
                }
            });

        $emailService = new EmailService($mailer, 'test@example.com');
        $emailDto = (new EmailDto())
            ->setSubject('Test')
            ->setToAddress('test@example.com')
            ->setBcc(array_map(
                fn (int $i) => new Address("bcc$i@example.com"),
                range(1, 61)
            ))
            ->setKind(EmailKind::Test)
            ->setText('Test TEXT')
            ->setHtml('Test HTML');

        $emailService->send($emailDto);
    }
}
