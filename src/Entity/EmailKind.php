<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Mime\Header\Headers;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EmailKind: string implements TranslatableInterface
{
    case Transactional = 'transactional';
    case Marketing = 'marketing';
    case Test = 'test';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::Transactional => $translator->trans('email-kind.transactional', locale: $locale),
            self::Marketing => $translator->trans('email-kind.marketing', locale: $locale),
            self::Test => $translator->trans('email-kind.test', locale: $locale),
        };
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromEmailHeader(Headers $headers): self
    {
        $kind = $headers->getHeaderBody('X-Email-Kind');
        if (!\is_string($kind)) {
            throw new \InvalidArgumentException('The email kind header is missing or is invalid type.');
        }

        return match ($kind) {
            'transactional' => self::Transactional,
            'marketing' => self::Marketing,
            'test' => self::Test,
            default => throw new \InvalidArgumentException("Invalid email kind: $kind"),
        };
    }

    public function addToEmailHeader(Headers $headers): Headers
    {
        return $headers->addTextHeader('X-Email-Kind', $this->value);
    }
}
