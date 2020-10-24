<?php

namespace App\Tests\Functional\Repository\Admin;

use App\Entity\Admin\User;
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
     * @covers \App\DataFixtures\Admin\UserFixtures::load
     */
    public function testFixtures(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);

        $users = $userRepository->findAll();

        $this->assertCount(104, $users);

        $superAdmin = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);
        $this->assertNotNull($superAdmin);
        $this->assertCount(2, $superAdmin->getRoles());
        $this->assertSame(User::ROLE_SUPER_ADMIN, $superAdmin->getRoles()[0]);
        $this->assertSame(User::ROLE_USER, $superAdmin->getRoles()[1]);
        $this->assertTrue($superAdmin->isEnabled());
        $this->assertSame('Super', $superAdmin->getFirstName());
        $this->assertSame('Admin', $superAdmin->getLastName());

        $admin = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->assertNotNull($admin);
        $this->assertCount(2, $admin->getRoles());
        $this->assertSame(User::ROLE_ADMIN, $admin->getRoles()[0]);
        $this->assertSame(User::ROLE_USER, $admin->getRoles()[1]);
        $this->assertTrue($admin->isEnabled());
        $this->assertSame('First Name', $admin->getFirstName());
        $this->assertSame('Last Name', $admin->getLastName());

        $user = $userRepository->findOneBy(['emailAddress' => 'user@example.com']);
        $this->assertNotNull($user);
        $this->assertCount(1, $user->getRoles());
        $this->assertSame(User::ROLE_USER, $user->getRoles()[0]);
        $this->assertTrue($user->isEnabled());
        $this->assertSame('Foo', $user->getFirstName());
        $this->assertSame('Bar', $user->getLastName());

        $disabledUser = $userRepository->findOneBy(['emailAddress' => 'disabled@example.com']);
        $this->assertNotNull($disabledUser);
        $this->assertCount(1, $disabledUser->getRoles());
        $this->assertSame(User::ROLE_USER, $disabledUser->getRoles()[0]);
        $this->assertFalse($disabledUser->isEnabled());
        $this->assertSame('Disabled', $disabledUser->getFirstName());
        $this->assertSame('User', $disabledUser->getLastName());
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
