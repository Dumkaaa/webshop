<?php

namespace App\Tests\Unit\Admin\Security;

use App\Admin\Security\UserProvider;
use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @covers \App\Admin\Security\UserProvider
 */
class UserProviderTest extends TestCase
{
    /**
     * @covers \App\Admin\Security\UserProvider::loadUserByUsername
     */
    public function testLoadUserByUsername(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userProvider = new UserProvider($entityManagerProphecy->reveal());

        $user = new User();
        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryProphecy->reveal());
        $userRepositoryProphecy->findOneBy(['emailAddress' => 'foo@bar.com'])->shouldBeCalledTimes(1)->willReturn($user);

        $this->assertSame($user, $userProvider->loadUserByUsername('foo@bar.com'));
    }

    /**
     * @covers \App\Admin\Security\UserProvider::loadUserByUsername
     */
    public function testLoadUserByUsernameNotFound(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userProvider = new UserProvider($entityManagerProphecy->reveal());

        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryProphecy->reveal());
        $userRepositoryProphecy->findOneBy(['emailAddress' => 'foo@bar.com'])->shouldBeCalledTimes(1)->willReturn(null);

        $this->expectException(UsernameNotFoundException::class);

        $userProvider->loadUserByUsername('foo@bar.com');
    }

    /**
     * @covers \App\Admin\Security\UserProvider::refreshUser
     */
    public function testRefreshUser(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userProvider = new UserProvider($entityManagerProphecy->reveal());

        $originalUser = new User();
        $originalUser->setEmailAddress('foo@bar.com');
        $refreshedUser = new User();
        $refreshedUser->setIsEnabled(true);

        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryProphecy->reveal());
        $userRepositoryProphecy->findOneBy(['emailAddress' => 'foo@bar.com'])->shouldBeCalledTimes(1)->willReturn($refreshedUser);

        $this->assertSame($refreshedUser, $userProvider->refreshUser($originalUser));
    }

    /**
     * @covers \App\Admin\Security\UserProvider::refreshUser
     */
    public function testRefreshUserUnsupported(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userProvider = new UserProvider($entityManagerProphecy->reveal());

        $user = $this->prophesize(UserInterface::class)->reveal();

        $this->expectException(UnsupportedUserException::class);

        $userProvider->refreshUser($user);
    }

    /**
     * @covers \App\Admin\Security\UserProvider::refreshUser
     */
    public function testRefreshUserDisabled(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userProvider = new UserProvider($entityManagerProphecy->reveal());

        $originalUser = new User();
        $originalUser->setEmailAddress('foo@bar.com');
        $refreshedUser = new User();

        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryProphecy->reveal());
        $userRepositoryProphecy->findOneBy(['emailAddress' => 'foo@bar.com'])->shouldBeCalledTimes(1)->willReturn($refreshedUser);

        $this->expectException(DisabledException::class);

        $userProvider->refreshUser($originalUser);
    }

    /**
     * @covers \App\Admin\Security\UserProvider::supportsClass
     */
    public function testSupportsClass(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class)->reveal();
        $userProvider = new UserProvider($entityManager);

        $this->assertTrue($userProvider->supportsClass(User::class));
        $this->assertFalse($userProvider->supportsClass('User'));
    }

    /**
     * @covers \App\Admin\Security\UserProvider::upgradePassword
     */
    public function testUpgradePassword(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $userProvider = new UserProvider($entityManagerProphecy->reveal());

        $user = new User();
        $user->setPassword('foo');

        $entityManagerProphecy->persist($user)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $userProvider->upgradePassword($user, 'bar');

        $this->assertSame('bar', $user->getPassword());
    }
}
