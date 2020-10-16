<?php

namespace App\Tests\Functional\Repository\Admin;

use App\Entity\Admin\User;
use App\Tests\Functional\EntityManagerTest;

class UserRepositoryTest extends EntityManagerTest
{
    public function testFixtures(): void
    {
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll()
        ;

        $this->assertCount(1, $users);

        $admin = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['emailAddress' => 'admin@example.com'])
        ;

        $this->assertNotNull($admin);
        $this->assertCount(2, $admin->getRoles());
        $this->assertSame(User::ROLE_ADMIN, $admin->getRoles()[0]);
        $this->assertSame(User::ROLE_USER, $admin->getRoles()[1]);
        $this->assertTrue($admin->isEnabled());
        $this->assertSame('First Name', $admin->getFirstName());
        $this->assertSame('Last Name', $admin->getLastName());
    }
}
