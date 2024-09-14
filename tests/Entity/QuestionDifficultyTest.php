<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\QuestionDifficulty;
use Monolog\Test\TestCase;

class QuestionDifficultyTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        foreach (QuestionDifficulty::cases() as $difficulty) {
            $this->assertSame(json_encode($difficulty->value), json_encode($difficulty));
        }
    }

    public function testJsonDeserialize(): void
    {
        foreach (QuestionDifficulty::cases() as $difficulty) {
            $this->assertSame($difficulty, QuestionDifficulty::fromString($difficulty->value));
        }
    }

    public function testJsonUnknownDeserialize(): void
    {
        $this->assertSame(QuestionDifficulty::Unspecified, QuestionDifficulty::fromString('UNKNOWN'));
    }
}
