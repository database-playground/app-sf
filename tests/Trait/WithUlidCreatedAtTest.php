<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Entity\Trait\WithUlidCreatedAt;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

class MockUlidClass
{
    use WithUlidCreatedAt;

    public ?Ulid $id = null;
}

class WithUlidCreatedAtTest extends TestCase
{
    public function testUlid(): void
    {
        $mock = new MockUlidClass();
        $mock->id = new Ulid();

        $this->assertEquals($mock->id->getDateTime(), $mock->getCreatedAt());
    }

    public function testNullId(): void
    {
        $mock = new MockUlidClass();
        $this->assertNull($mock->getCreatedAt());
    }
}
