<?php

namespace App\Tests\Functional\Repository\Admin;

use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;

/**
 * @covers \App\Repository\Admin\UserRepository
 */
class UserRepositoryTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            'UserFixtures',
        ];
    }

    /**
     * @covers \App\Repository\Admin\UserRepository::findEnabledByEmailAddresses
     */
    public function testFindEnabledByEmailAddresses(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);

        $emailAddresses = [
            'admin@example.com',
            'user@example.com',
            'disabled@example.com',
            'nonexisting@example.com',
        ];

        $enabledUsers = $userRepository->findEnabledByEmailAddresses($emailAddresses);

        $this->assertCount(2, $enabledUsers);
        $this->assertSame('admin@example.com', $enabledUsers[0]->getEmailAddress());
        $this->assertSame('user@example.com', $enabledUsers[1]->getEmailAddress());

        $disabledUsers = $userRepository->findEnabledByEmailAddresses($emailAddresses, false);

        $this->assertCount(1, $disabledUsers);
        $this->assertSame('disabled@example.com', $disabledUsers[0]->getEmailAddress());
    }
}
