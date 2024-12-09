<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\EmailDto\EmailDto;
use App\Entity\EmailKind;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

class EmailDtoTest extends TestCase
{
    public function testEmailDtoToEmail(): void
    {
        $emailDto = (new EmailDto())
            ->setSubject('Test subject')
            ->setToAddress(new Address('test@dbplay.pan93.com'))
            ->setKind(EmailKind::Test)
            ->setText('Test text')
            ->setHtml('<p>Test text</p>');

        $email = $emailDto->toEmail();

        self::assertEquals('Test subject', $email->getSubject());
        self::assertEquals('test@dbplay.pan93.com', $email->getTo()[0]->getAddress());
        self::assertEquals('Test text', $email->getTextBody());
        self::assertEquals('<p>Test text</p>', $email->getHtmlBody());

        $extractedKind = EmailKind::fromEmailHeader($email->getHeaders());
        self::assertEquals(EmailKind::Test, $extractedKind);
    }

    public function testEmailDtoToUser(): void
    {
        $user = (new User())
            ->setName('Test name')
            ->setEmail('test@dbplay.pan93.com');

        $emailDto = EmailDto::fromUser($user)
            ->setSubject('Test subject')
            ->setKind(EmailKind::Test)
            ->setText('Test text')
            ->setHtml('<p>Test text</p>');

        $email = $emailDto->toEmail();

        self::assertEquals('Test subject', $email->getSubject());
        self::assertEquals('"Test name" <test@dbplay.pan93.com>', $email->getTo()[0]->toString());
        self::assertEquals('Test text', $email->getTextBody());
        self::assertEquals('<p>Test text</p>', $email->getHtmlBody());

        $extractedKind = EmailKind::fromEmailHeader($email->getHeaders());
        self::assertEquals(EmailKind::Test, $extractedKind);
    }
}
