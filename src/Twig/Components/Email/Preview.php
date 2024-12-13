<?php

declare(strict_types=1);

namespace App\Twig\Components\Email;

use App\Entity\EmailDto\SentEmailDto;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Preview
{
    public SentEmailDto $emailDto;
}
