<?php

declare(strict_types=1);

namespace App\Entity;

enum QuestionDifficulty: string
{
    case Unspecified = 'UNSPECIFIED';
    case Easy = 'EASY';
    case Medium = 'MEDIUM';
    case Hard = 'HARD';
}
