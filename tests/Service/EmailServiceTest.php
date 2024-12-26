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

/**
 * @internal
 *
 * @coversNothing
 */
final class EmailServiceTest extends TestCase
{
    public function testSendWithoutBcc(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(static function (Email $email): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test2@gmail.com', $email->getTo()[0]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());
            })
        ;

        $emailService = new EmailService($mailer, 'test@example.com', '', 10);
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
            ->willReturnCallback(static function (Email $email): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test2@gmail.com', $email->getTo()[0]->getAddress());
                self::assertSame('me@pan93.com', $email->getBcc()[0]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());
            })
        ;

        $emailService = new EmailService($mailer, 'test@example.com', '', 10);
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

    public function testSendWithChunkedBcc(): void
    {
        $invokedCount = self::exactly(2);
        $lastSendAt = new \DateTimeImmutable();

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($invokedCount)
            ->method('send')
            ->willReturnCallback(static function (Email $email) use (&$invokedCount, &$lastSendAt): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());

                switch ($invokedCount->numberOfInvocations()) {
                    case 1:
                        self::assertCount(10, $email->getBcc());
                        self::assertSame('bcc1@example.com', $email->getBcc()[0]->getAddress());
                        self::assertSame('bcc10@example.com', $email->getBcc()[9]->getAddress());
                        $lastSendAt = $email->getDate();

                        break;

                    case 2:
                        self::assertCount(1, $email->getBcc());
                        self::assertSame('bcc11@example.com', $email->getBcc()[0]->getAddress());
                        self::assertGreaterThan($lastSendAt, $email->getDate());
                }
            })
        ;

        $emailService = new EmailService($mailer, 'test@example.com', '', 10);
        $emailDto = (new EmailDto())
            ->setSubject('Test')
            ->setToAddress('test@example.com')
            ->setBcc(array_map(
                static fn (int $i) => new Address("bcc{$i}@example.com"),
                range(1, 11)
            ))
            ->setKind(EmailKind::Test)
            ->setText('Test TEXT')
            ->setHtml('Test HTML')
        ;

        $emailService->send($emailDto);
    }

    public function testSendToTestEmail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(static function (Email $email): void {
                self::assertSame('Test', $email->getSubject());
                self::assertSame('test@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test+target@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Test TEXT', $email->getTextBody());
                self::assertSame('Test HTML', $email->getHtmlBody());
            })
        ;

        $emailService = new EmailService($mailer, 'test@example.com', 'test+target@example.com', 10);

        $emailDto = (new EmailDto())
            ->setSubject('Test')
            ->setKind(EmailKind::Test)
            ->setText('Test TEXT')
            ->setHtml('Test HTML')
        ;

        $emailService->sendToTest($emailDto);
    }
}
