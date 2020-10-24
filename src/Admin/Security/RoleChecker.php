<?php

namespace App\Admin\Security;

use App\Entity\Admin\User;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Service for checking \App\Entity\Admin\User::class roles.
 */
class RoleChecker
{
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Determines if the given user has the given role, this also includes roles received via the role hierarchy determined in the security.yaml.
     */
    public function hasRole(User $user, string $role): bool
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        return in_array($role, $reachableRoles);
    }
}
