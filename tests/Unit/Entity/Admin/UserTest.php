<?php

namespace App\Tests\Unit\Entity\Admin;

use App\ActionLog\LoggableObjectInterface;
use App\Entity\Admin\User;
use App\Timestampable\TimestampableInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @covers \App\Entity\Admin\User
 */
class UserTest extends TestCase
{
    public function testConstruct(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
        $this->assertCount(1, $user->getRoles());
        $this->assertSame(User::ROLE_USER, $user->getRoles()[0]);
        $this->assertSame(User::ROLE_USER, $user->getMainRole());
        $this->assertFalse($user->hasRole(User::ROLE_ADMIN));
        $this->assertFalse($user->isEnabled());
        $this->assertNull($user->getLastLoginAt());
        $this->assertNull($user->getLastActiveAt());
        $this->assertFalse($user->isActive());
        $this->assertNull($user->getPlainPassword());
        $this->assertNull($user->getSalt());

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertInstanceOf(TimestampableInterface::class, $user);
        $this->assertInstanceOf(LoggableObjectInterface::class, $user);
        $this->assertSame([
            'lastLoginAt',
            'lastActiveAt',
        ], $user->getNonLoggableProperties());
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

        $this->assertSame('First Name Last Name', $user->getFullName());

        $user->setPassword('P4$$w0rd');
        $this->assertSame('P4$$w0rd', $user->getPassword());

        $user->setRoles([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN]);
        $this->assertCount(3, $user->getRoles());
        $this->assertSame(User::ROLE_SUPER_ADMIN, $user->getRoles()[0]);
        $this->assertSame(User::ROLE_ADMIN, $user->getRoles()[1]);
        $this->assertSame(User::ROLE_USER, $user->getRoles()[2]);
        $this->assertSame(User::ROLE_SUPER_ADMIN, $user->getMainRole());
        $user->setRoles([User::ROLE_ADMIN]);
        $this->assertSame(User::ROLE_ADMIN, $user->getMainRole());

        $user->setIsEnabled(true);
        $this->assertTrue($user->isEnabled());

        $user->setLastLoginAt(new \DateTimeImmutable('11-11-2011 midnight'));
        $this->assertEquals(new \DateTimeImmutable('11-11-2011 midnight'), $user->getLastLoginAt());

        $user->setLastActiveAt(new \DateTimeImmutable('11-11-2011 midnight'));
        $this->assertEquals(new \DateTimeImmutable('11-11-2011 midnight'), $user->getLastActiveAt());
        $this->assertFalse($user->isActive());

        $user->setLastActiveAt(new \DateTimeImmutable('now'));
        $this->assertTrue($user->isActive());

        $user->setPlainPassword('Pl41nP4$$w0rd');
        $this->assertSame('Pl41nP4$$w0rd', $user->getPlainPassword());
    }

    /**
     * @covers \App\Entity\Admin\User::eraseCredentials
     */
    public function testEraseCredentials(): void
    {
        $user = new User();

        $user->setPlainPassword('Pl41nP4$$w0rd');
        $this->assertSame('Pl41nP4$$w0rd', $user->getPlainPassword());

        $user->eraseCredentials();
        $this->assertNull($user->getPlainPassword());
    }
}
