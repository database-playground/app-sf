<?php

declare(strict_types=1);

namespace App\Twig\Components\Email;

use App\Entity\EmailDto\EmailDto;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Preview
{
    public EmailDto $emailDto;
}
