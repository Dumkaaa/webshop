<?php

namespace App\Tests\Admin\Security\Voter;

use App\Admin\Security\Voter\AdminUserVoter;
use App\Entity\Admin\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

class AdminUserVoterTest extends TestCase
{
    public function testDoesNotSupport(): void
    {
        // Don't prophesize methods as the tests should not pass the supports anyways.
        $security = $this->prophesize(Security::class)->reveal();
        $voter = new AdminUserVoter($security);

        $token = $this->prophesize(TokenInterface::class)->reveal();
        $subject = new User();

        // Test with invalid attributes.
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, $subject, ['random']));

        // Test with invalid attributes for non-subject.
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, null, [
            AdminUserVoter::EDIT,
            AdminUserVoter::UPDATE_STATUS,
            AdminUserVoter::UPDATE_ROLES,
        ]));
    }

    public function testUnauthorizedVote(): void
    {
        $security = $this->prophesize(Security::class)->reveal();
        $voter = new AdminUserVoter($security);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(5)->willReturn(null);
        $token = $tokenProphecy->reveal();

        $subject = new User();

        // If the token has no authorized user the voter should always deny access.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [
            AdminUserVoter::VIEW,
            AdminUserVoter::CREATE,
            AdminUserVoter::EDIT,
            AdminUserVoter::UPDATE_STATUS,
            AdminUserVoter::UPDATE_ROLES,
        ]));
    }

    public function testNonAdminVote(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted(User::ROLE_ADMIN)->shouldBeCalledTimes(5)->willReturn(false);
        $voter = new AdminUserVoter($securityProphecy->reveal());

        $user = new User();
        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(5)->willReturn($user);
        $token = $tokenProphecy->reveal();

        $subject = new User();

        // The user should never have access when it does not have the admin role.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [
            AdminUserVoter::VIEW,
            AdminUserVoter::CREATE,
            AdminUserVoter::EDIT,
            AdminUserVoter::UPDATE_STATUS,
            AdminUserVoter::UPDATE_ROLES,
        ]));
    }

    public function testVoteView(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted(User::ROLE_ADMIN)->shouldBeCalledTimes(1)->willReturn(true);
        $voter = new AdminUserVoter($securityProphecy->reveal());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $user = new User();
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user);
        $token = $tokenProphecy->reveal();

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, null, [AdminUserVoter::VIEW]));
    }

    public function testVoteCreate(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted(User::ROLE_ADMIN)->shouldBeCalledTimes(1)->willReturn(true);
        $voter = new AdminUserVoter($securityProphecy->reveal());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $user = new User();
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user);
        $token = $tokenProphecy->reveal();

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, null, [AdminUserVoter::CREATE]));
    }

    public function testVoteEdit(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $voter = new AdminUserVoter($securityProphecy->reveal());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $token = $tokenProphecy->reveal();

        $user = new User();
        $subject = new User();

        $tokenProphecy->getUser()->shouldBeCalledTimes(6)->willReturn($subject, $user, $subject, $user, $user, $user);
        $securityProphecy->isGranted(User::ROLE_ADMIN)->shouldBeCalledTimes(6)->willReturn(true);
        $securityProphecy->isGranted(User::ROLE_SUPER_ADMIN, $subject)->shouldBeCalledTimes(6)->willReturn(true, true, false, false, false, false);
        $securityProphecy->isGranted(User::ROLE_ADMIN, $subject)->shouldBeCalledTimes(4)->willReturn(true, true, true, false);

        // Test with super admin subject while being the same user as the subject.
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [AdminUserVoter::EDIT]));
        // Test with super admin subject while being a different user as the subject.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [AdminUserVoter::EDIT]));

        // Test with admin subject while being the same user as the subject.
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [AdminUserVoter::EDIT]));
        $securityProphecy->isGranted(User::ROLE_SUPER_ADMIN)->shouldBeCalledTimes(2)->willReturn(false, true);
        // Test with admin subject while being a different user as the subject.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [AdminUserVoter::EDIT]));
        // Test with admin subject while being a different user as the subject and being a super admin.
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [AdminUserVoter::EDIT]));

        // Test with a default user as subject.
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [AdminUserVoter::EDIT]));
    }

    public function testVoteUpdateStatus(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $voter = new AdminUserVoter($securityProphecy->reveal());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $token = $tokenProphecy->reveal();

        $user = new User();
        $subject = new User();

        $tokenProphecy->getUser()->shouldBeCalledTimes(6)->willReturn($subject, $user, $subject, $user, $user, $user);
        $securityProphecy->isGranted(User::ROLE_ADMIN)->shouldBeCalledTimes(6)->willReturn(true);
        $securityProphecy->isGranted(User::ROLE_SUPER_ADMIN, $subject)->shouldBeCalledTimes(6)->willReturn(true, true, false, false, false, false);
        $securityProphecy->isGranted(User::ROLE_ADMIN, $subject)->shouldBeCalledTimes(4)->willReturn(true, true, true, false);

        // Test with super admin subject while being the same user as the subject.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_STATUS]));
        // Test with super admin subject while being a different user as the subject.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_STATUS]));

        $securityProphecy->isGranted(User::ROLE_SUPER_ADMIN)->shouldBeCalledTimes(3)->willReturn(false, false, true);
        // Test with admin subject while being the same user as the subject.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_STATUS]));
        // Test with admin subject while being a different user as the subject.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_STATUS]));
        // Test with admin subject while being a different user as the subject and being a super admin.
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_STATUS]));

        // Test with a default user as subject.
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_STATUS]));
    }

    public function testVoteUpdateRoles(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $voter = new AdminUserVoter($securityProphecy->reveal());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $token = $tokenProphecy->reveal();

        $user = new User();
        $subject = new User();

        $tokenProphecy->getUser()->shouldBeCalledTimes(3)->willReturn($user);
        $securityProphecy->isGranted(User::ROLE_ADMIN)->shouldBeCalledTimes(3)->willReturn(true);
        $securityProphecy->isGranted(User::ROLE_SUPER_ADMIN, $subject)->shouldBeCalledTimes(3)->willReturn(false, true, false);
        $securityProphecy->isGranted(User::ROLE_SUPER_ADMIN)->shouldBeCalledTimes(2)->willReturn(true, false);

        // Test with a default user subject and super admin user.
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_ROLES]));
        // Test with a super admin subject and super admin user.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_ROLES]));
        // Test with a default user subject and admin user.
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [AdminUserVoter::UPDATE_ROLES]));
    }
}
