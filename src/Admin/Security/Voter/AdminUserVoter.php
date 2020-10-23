<?php

namespace App\Admin\Security\Voter;

use App\Entity\Admin\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AdminUserVoter extends Voter
{
    const VIEW = 'VIEW_ADMIN_USER';
    const CREATE = 'CREATE_ADMIN_USER';
    const EDIT = 'EDIT_ADMIN_USER';
    const UPDATE_STATUS = 'UPDATE_ADMIN_USER_STATUS';
    const UPDATE_ROLES = 'UPDATE_ADMIN_USER_ROLES';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject): bool
    {
        // Attributes that don't need the user entity itself also support a null subject.
        if (in_array($attribute, [self::VIEW, self::CREATE])
            && ($subject instanceof User || null === $subject)) {
            return true;
        }

        return in_array($attribute, [self::EDIT, self::UPDATE_STATUS, self::UPDATE_ROLES])
            && $subject instanceof User;
    }

    /**
     * @param ?User $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // Never grant access if the user is not logged in.
        if (!$user instanceof User) {
            return false;
        }

        // Never grant access for non-admins.
        if (!$this->security->isGranted(User::ROLE_ADMIN)) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
            case self::UPDATE_STATUS:
                /** @var User $subject */
                if ($this->security->isGranted(User::ROLE_SUPER_ADMIN, $subject)) {
                    // Nobody can edit super admins except themselves (except editing states).
                    return self::UPDATE_STATUS !== $attribute && $subject === $user;
                }
                if ($this->security->isGranted(User::ROLE_ADMIN, $subject)) {
                    // Only super admins and the users themselves can edit admins (except editing states for the users themselves).
                    return (self::UPDATE_STATUS !== $attribute && $subject === $user) || $this->security->isGranted(User::ROLE_SUPER_ADMIN);
                }

                // It's a basic user, allow editing for admins.
                return true;

            case self::UPDATE_ROLES:
                // Only super admins can update roles, and not those of super admins.
                /* @var User $subject */
                return !$this->security->isGranted(User::ROLE_SUPER_ADMIN, $subject)
                    && $this->security->isGranted(User::ROLE_SUPER_ADMIN);

            case self::VIEW:
            case self::CREATE:
            default: // The code will never reach default because of the supports function, but otherwise the function does not have a default return value.
                return true;
        }
    }
}
