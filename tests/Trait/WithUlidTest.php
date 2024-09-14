<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Entity\Trait\WithUlid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

class WithUlidTest extends TestCase
{
    public function testUlidGet(): void
    {
        $ulid = new Ulid();
        $mock = new MockUlidClass($ulid);

        $this->assertEquals($ulid, $mock->getId());
    }

    public function testUlidGetNull(): void
    {
        $mock = new MockUlidClass(null);

        $this->assertNull($mock->getId());
    }

    public function testUlidCreatedAt(): void
    {
        $ulid = new Ulid();
        $mock = new MockUlidClass($ulid);

        $this->assertEquals($ulid->getDateTime(), $mock->getCreatedAt());
    }

    public function testUlidCreatedAtNull(): void
    {
        $mock = new MockUlidClass(null);

        $this->assertNull($mock->getCreatedAt());
    }
}

class MockUlidClass
{
    use WithUlid;

    public function __construct(
        ?Ulid $id,
    ) {
        $this->id = $id;
    }
}
