<?php

namespace App\Tests\Unit\DataFixtures\Admin;

use App\DataFixtures\Admin\UserFixtures;
use App\DataFixtures\FixtureGroupInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @covers \App\DataFixtures\Admin\UserFixtures
 */
class UserFixturesTest extends TestCase
{
    public function testConstruct(): void
    {
        $passwordEncoder = $this->prophesize(UserPasswordEncoderInterface::class)->reveal();
        $fixtures = new UserFixtures($passwordEncoder);

        $this->assertInstanceOf(FixtureGroupInterface::class, $fixtures);
    }

    /**
     * @covers \App\DataFixtures\Admin\UserFixtures::getGroups
     */
    public function testGetGroups(): void
    {
        $this->assertSame([
            FixtureGroupInterface::ADMIN,
            FixtureGroupInterface::ADMIN_USER,
        ], UserFixtures::getGroups());
    }
}
