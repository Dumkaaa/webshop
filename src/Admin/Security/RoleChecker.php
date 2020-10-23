<?php

namespace App\Admin\Security;

use App\Entity\Admin\User;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class RoleChecker
{
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function hasRole(User $user, string $role): bool
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        return in_array($role, $reachableRoles);
    }
}
