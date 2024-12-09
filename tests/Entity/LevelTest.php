<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LevelTest extends TestCase
{
    #[DataProvider('fromPercentDataProvider')]
    public function testFromPercent(float $percent, Level $expected): void
    {
        $level = Level::fromPercent($percent);
        self::assertSame($expected, $level);
    }

    /**
     * @return iterable<array{float, Level}>
     */
    public static function fromPercentDataProvider(): iterable
    {
        yield [0, Level::Starter];
        yield [4.9, Level::Starter];
        yield [5, Level::Beginner];
        yield [19.9, Level::Beginner];
        yield [20, Level::Intermediate];
        yield [39.9, Level::Intermediate];
        yield [40, Level::Advanced];
        yield [64.9, Level::Advanced];
        yield [65, Level::Expert];
        yield [89.9, Level::Expert];
        yield [90, Level::Master];
        yield [100, Level::Master];
    }
}
