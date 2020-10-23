<?php

namespace App\Tests\Functional\Repository\Admin;

use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;

class UserRepositoryTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            'UserFixtures',
        ];
    }

    public function testFixtures(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);

        $users = $userRepository->findAll();

        $this->assertCount(103, $users);

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
    }
}
