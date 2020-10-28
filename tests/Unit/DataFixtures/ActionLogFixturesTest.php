<?php

namespace App\Tests\Unit\DataFixtures;

use App\DataFixtures\ActionLogFixtures;
use App\DataFixtures\Admin\UserFixtures;
use App\DataFixtures\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\DataFixtures\ActionLogFixtures
 */
class ActionLogFixturesTest extends TestCase
{
    public function testConstruct(): void
    {
        $fixtures = new ActionLogFixtures();

        $this->assertInstanceOf(FixtureGroupInterface::class, $fixtures);
        $this->assertInstanceOf(DependentFixtureInterface::class, $fixtures);
    }

    /**
     * @covers \App\DataFixtures\ActionLogFixtures::getGroups
     */
    public function testGetGroups(): void
    {
        $this->assertSame([
            FixtureGroupInterface::ADMIN,
            FixtureGroupInterface::ADMIN_LOGS,
        ], ActionLogFixtures::getGroups());
    }

    /**
     * @covers \App\DataFixtures\ActionLogFixtures::getDependencies
     */
    public function testGetDependencies()
    {
        $fixtures = new ActionLogFixtures();

        $this->assertSame([
            UserFixtures::class,
        ], $fixtures->getDependencies());
    }
}