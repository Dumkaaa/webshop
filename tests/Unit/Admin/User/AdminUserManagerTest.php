<?php

namespace App\Tests\Unit\Admin\User;

use App\Admin\Security\Voter\AdminUserVoter;
use App\Admin\User\AdminUserManager;
use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class AdminUserManagerTest extends TestCase
{
    public function testToggleEnabledAccessDenied(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $manager = new AdminUserManager($entityManagerProphecy->reveal(), $securityProphecy->reveal());
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);

        $user = new User();
        $user->setEmailAddress('foo@bar.com');

        $userRepositoryProphecy->findEnabledByEmailAddresses(['foo@bar.com'], false)->shouldBeCalledTimes(1)->willReturn([$user]);
        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryProphecy->reveal());
        $securityProphecy->isGranted(AdminUserVoter::UPDATE_ROLES, $user)->shouldBeCalledTimes(1)->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You are not allowed to update the role of the user "foo@bar.com".');

        $manager->toggleEnabled(['foo@bar.com']);
    }

    public function testToggleEnabled(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $manager = new AdminUserManager($entityManagerProphecy->reveal(), $securityProphecy->reveal());
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);

        $user1 = new User();
        $user1->setEmailAddress('foo@bar.com');
        $user2 = new User();
        $user2->setEmailAddress('bar@foo.com');

        $userRepositoryProphecy->findEnabledByEmailAddresses(['foo@bar.com', 'bar@foo.com'], false)->shouldBeCalledTimes(1)->willReturn([$user1, $user2]);
        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryProphecy->reveal());
        $entityManagerProphecy->persist($user1)->shouldBeCalledTimes(1);
        $entityManagerProphecy->persist($user2)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);
        $securityProphecy->isGranted(AdminUserVoter::UPDATE_ROLES, $user1)->shouldBeCalledTimes(1)->willReturn(true);
        $securityProphecy->isGranted(AdminUserVoter::UPDATE_ROLES, $user2)->shouldBeCalledTimes(1)->willReturn(true);

        $this->assertFalse($user1->isEnabled());
        $this->assertFalse($user2->isEnabled());

        $manager->toggleEnabled(['foo@bar.com', 'bar@foo.com']);

        $this->assertTrue($user1->isEnabled());
        $this->assertTrue($user2->isEnabled());
    }

    public function testToggleEnabledDisable(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $manager = new AdminUserManager($entityManagerProphecy->reveal(), $securityProphecy->reveal());
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);

        $user1 = new User();
        $user1->setEmailAddress('foo@bar.com');
        $user1->setIsEnabled(true);
        $user2 = new User();
        $user2->setEmailAddress('bar@foo.com');
        $user2->setIsEnabled(true);

        $userRepositoryProphecy->findEnabledByEmailAddresses(['foo@bar.com', 'bar@foo.com'], true)->shouldBeCalledTimes(1)->willReturn([$user1, $user2]);
        $entityManagerProphecy->getRepository(User::class)->shouldBeCalledTimes(1)->willReturn($userRepositoryProphecy->reveal());
        $entityManagerProphecy->persist($user1)->shouldBeCalledTimes(1);
        $entityManagerProphecy->persist($user2)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);
        $securityProphecy->isGranted(AdminUserVoter::UPDATE_ROLES, $user1)->shouldBeCalledTimes(1)->willReturn(true);
        $securityProphecy->isGranted(AdminUserVoter::UPDATE_ROLES, $user2)->shouldBeCalledTimes(1)->willReturn(true);

        $this->assertTrue($user1->isEnabled());
        $this->assertTrue($user2->isEnabled());

        $manager->toggleEnabled(['foo@bar.com', 'bar@foo.com'], false);

        $this->assertFalse($user1->isEnabled());
        $this->assertFalse($user2->isEnabled());
    }
}
