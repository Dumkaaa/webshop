<?php

namespace App\Tests\Functional\Repository;

use App\DataFixtures\FixtureGroupInterface;
use App\Repository\ActionLogRepository;
use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;

/**
 * @covers \App\Repository\ActionLogRepository
 */
class ActionLogRepositoryTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            FixtureGroupInterface::ADMIN_LOGS,
        ];
    }

    /**
     * @covers \App\Repository\ActionLogRepository::findGroupedForUserBetween
     */
    public function testFindGroupedForUserBetween(): void
    {
        /** @var ActionLogRepository $actionLogRepository */
        $actionLogRepository = static::$container->get(ActionLogRepository::class);
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $user = $userRepository->findOneBy(['emailAddress' => 'user@example.com']);

        $actionLogs = $actionLogRepository->findGroupedForUserBetween(
            $admin,
            new \DateTimeImmutable('-1 minute'),
            new \DateTimeImmutable('now')
        );
        $this->assertCount(2, $actionLogs);

        $actionLogs = $actionLogRepository->findGroupedForUserBetween(
            $admin,
            new \DateTimeImmutable('-1 month'),
            new \DateTimeImmutable('-1 minute')
        );
        $this->assertCount(0, $actionLogs);

        $actionLogs = $actionLogRepository->findGroupedForUserBetween(
            $user,
            new \DateTimeImmutable('-1 minute'),
            new \DateTimeImmutable('now')
        );
        $this->assertCount(0, $actionLogs);
    }
}
