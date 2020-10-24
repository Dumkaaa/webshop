<?php

namespace App\Admin\Security;

use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User provider for the admin environment, provides \App\Entity\Admin\User::class.
 */
class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername(string $username): User
    {
        /** @var UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['emailAddress' => $username]);

        if (!$user) {
            $exception = new UsernameNotFoundException();
            $exception->setUsername($username);
            throw $exception;
        }

        return $user;
    }

    /**
     * @throws UnsupportedUserException
     * @throws DisabledException
     * @throws UsernameNotFoundException
     */
    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $user = $this->loadUserByUsername($user->getUsername());

        if (!$user->isEnabled()) {
            throw new DisabledException();
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    /**
     * @param User $user
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        $user->setPassword($newEncodedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
