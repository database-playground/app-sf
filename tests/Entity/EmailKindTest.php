<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\EmailKind;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Header\Headers;

/**
 * @internal
 *
 * @coversNothing
 */
final class EmailKindTest extends TestCase
{
    public function testEmailKindAddHeader(): void
    {
        $header = new Headers();
        $kind = EmailKind::Transactional;

        $header = $kind->addToEmailHeader($header);

        self::assertSame($kind->value, $header->get(EmailKind::EMAIL_HEADER)?->getBodyAsString());
    }

    public function testEmailKindExtractHeader(): void
    {
        $kind = EmailKind::Transactional;
        $header = new Headers();
        $header->addTextHeader(EmailKind::EMAIL_HEADER, $kind->value);

        $extractedKind = EmailKind::fromEmailHeader($header);

        self::assertSame($kind, $extractedKind);
    }

    public function testEmailKindNoHeader(): void
    {
        $header = new Headers();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The email kind header is missing or is invalid type.');

        EmailKind::fromEmailHeader($header);
    }

    public function testEmailKindInvalidHeader(): void
    {
        $header = new Headers();
        $header->addTextHeader(EmailKind::EMAIL_HEADER, 'invalid###');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email kind: invalid###');

        EmailKind::fromEmailHeader($header);
    }
}
