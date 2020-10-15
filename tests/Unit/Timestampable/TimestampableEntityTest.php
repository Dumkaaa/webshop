<?php

namespace App\Tests\Unit\Timestampable;

use App\Entity\Admin\User;
use App\Timestampable\TimestampableInterface;
use PHPUnit\Framework\TestCase;

class TimestampableEntityTest extends TestCase
{
    public function testSetters(): void
    {
        $entity = new User();

        $this->assertInstanceOf(TimestampableInterface::class, $entity);

        $entity->setCreatedAt(new \DateTimeImmutable('11-11-2011 midnight'));
        $this->assertEquals(new \DateTimeImmutable('11-11-2011 midnight'), $entity->getCreatedAt());

        $entity->setLastUpdatedAt(new \DateTimeImmutable('02-02-2020 midnight'));
        $this->assertEquals(new \DateTimeImmutable('02-02-2020 midnight'), $entity->getLastUpdatedAt());
    }
}
