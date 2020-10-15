<?php

namespace App\Tests\Unit\Entity\Admin;

use App\Entity\Admin\User;
use App\Timestampable\TimestampableInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserTest extends TestCase
{
    public function testConstruct(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
        $this->assertCount(1, $user->getRoles());
        $this->assertSame(User::ROLE_USER, $user->getRoles()[0]);
        $this->assertFalse($user->isEnabled());
        $this->assertNull($user->getPlainPassword());
        $this->assertNull($user->getSalt());

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertInstanceOf(TimestampableInterface::class, $user);
    }

    public function testSetters(): void
    {
        $user = new User();

        $user->setEmailAddress('admin@example.com');
        $this->assertSame('admin@example.com', $user->getEmailAddress());
        $this->assertSame('admin@example.com', $user->getUsername());

        $user->setFirstName('First Name');
        $this->assertSame('First Name', $user->getFirstName());

        $user->setLastName('Last Name');
        $this->assertSame('Last Name', $user->getLastName());

        $user->setPassword('P4$$w0rd');
        $this->assertSame('P4$$w0rd', $user->getPassword());

        $user->setRoles([User::ROLE_ADMIN]);
        $this->assertCount(2, $user->getRoles());
        $this->assertSame(User::ROLE_ADMIN, $user->getRoles()[0]);
        $this->assertSame(User::ROLE_USER, $user->getRoles()[1]);

        $user->setIsEnabled(true);
        $this->assertTrue($user->isEnabled());

        $user->setPlainPassword('Pl41nP4$$w0rd');
        $this->assertSame('Pl41nP4$$w0rd', $user->getPlainPassword());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();

        $user->setPlainPassword('Pl41nP4$$w0rd');
        $this->assertSame('Pl41nP4$$w0rd', $user->getPlainPassword());

        $user->eraseCredentials();
        $this->assertNull($user->getPlainPassword());
    }
}
