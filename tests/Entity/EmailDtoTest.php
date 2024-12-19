<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\EmailDto\EmailDto;
use App\Entity\EmailKind;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

/**
 * @internal
 *
 * @coversNothing
 */
final class EmailDtoTest extends TestCase
{
    public function testEmailDtoToEmail(): void
    {
        $emailDto = (new EmailDto())
            ->setSubject('Test subject')
            ->setToAddress(new Address('test@dbplay.pan93.com'))
            ->setKind(EmailKind::Test)
            ->setText('Test text')
            ->setHtml('<p>Test text</p>')
        ;

        $email = $emailDto->toEmail();

        self::assertSame('Test subject', $email->getSubject());
        self::assertSame('test@dbplay.pan93.com', $email->getTo()[0]->getAddress());
        self::assertSame('Test text', $email->getTextBody());
        self::assertSame('<p>Test text</p>', $email->getHtmlBody());

        $extractedKind = EmailKind::fromEmailHeader($email->getHeaders());
        self::assertSame(EmailKind::Test, $extractedKind);
    }

    public function testEmailDtoToUser(): void
    {
        $user = (new User())
            ->setName('Test name')
            ->setEmail('test@dbplay.pan93.com')
        ;

        $emailDto = EmailDto::fromUser($user)
            ->setSubject('Test subject')
            ->setKind(EmailKind::Test)
            ->setText('Test text')
            ->setHtml('<p>Test text</p>')
        ;

        $email = $emailDto->toEmail();

        self::assertSame('Test subject', $email->getSubject());
        self::assertSame('"Test name" <test@dbplay.pan93.com>', $email->getTo()[0]->toString());
        self::assertSame('Test text', $email->getTextBody());
        self::assertSame('<p>Test text</p>', $email->getHtmlBody());

        $extractedKind = EmailKind::fromEmailHeader($email->getHeaders());
        self::assertSame(EmailKind::Test, $extractedKind);
    }
}
