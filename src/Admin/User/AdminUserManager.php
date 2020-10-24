<?php

namespace App\Admin\User;

use App\Admin\Security\Voter\AdminUserVoter;
use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * Manager service for \App\Entity\Admin\User::class.
 */
class AdminUserManager
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * Sets the isEnabled property of the users for the given email addresses to the given value.
     *
     * @param array<string> $emailAddresses
     */
    public function toggleEnabled(array $emailAddresses, bool $enable = true): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        // Select all users for the given email addresses, ignore the ones that already have the desired isEnabled status.
        $users = $userRepository->findEnabledByEmailAddresses($emailAddresses, !$enable);

        foreach ($users as $user) {
            if (!$this->security->isGranted(AdminUserVoter::UPDATE_ROLES, $user)) {
                throw new AccessDeniedException(sprintf('You are not allowed to update the role of the user "%s".', $user->getEmailAddress()));
            }

            $user->setIsEnabled($enable);
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }
}
