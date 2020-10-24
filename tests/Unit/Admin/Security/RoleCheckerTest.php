<?php

namespace App\Tests\Unit\Admin\Security;

use App\Admin\Security\RoleChecker;
use App\Entity\Admin\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @covers \App\Admin\Security\RoleChecker
 */
class RoleCheckerTest extends TestCase
{
    /**
     * @covers \App\Admin\Security\RoleChecker::hasRole
     */
    public function testHasRole(): void
    {
        $roleHierarchyProphecy = $this->prophesize(RoleHierarchyInterface::class);
        $roleHierarchyProphecy->getReachableRoleNames([User::ROLE_USER])->shouldBeCalledTimes(5)->willReturn([
            User::ROLE_USER,
        ], [
            User::ROLE_USER,
        ], [
            User::ROLE_USER,
            User::ROLE_ADMIN,
        ], [
            User::ROLE_USER,
            User::ROLE_ADMIN,
        ], [
            User::ROLE_USER,
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ]);

        $roleChecker = new RoleChecker($roleHierarchyProphecy->reveal());
        $user = new User();

        $this->assertTrue($roleChecker->hasRole($user, User::ROLE_USER));
        $this->assertFalse($roleChecker->hasRole($user, User::ROLE_ADMIN));
        $this->assertTrue($roleChecker->hasRole($user, User::ROLE_ADMIN));
        $this->assertFalse($roleChecker->hasRole($user, User::ROLE_SUPER_ADMIN));
        $this->assertTrue($roleChecker->hasRole($user, User::ROLE_SUPER_ADMIN));
    }
}
