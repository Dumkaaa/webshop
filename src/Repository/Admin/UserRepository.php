<?php

namespace App\Repository\Admin;

use App\Entity\Admin\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param array<string> $emailAddresses
     * @param bool          $enabled        set to false if you want to only select disabled users instead
     *
     * @return User[]
     */
    public function findEnabledByEmailAddresses(array $emailAddresses, bool $enabled = true): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.emailAddress IN (:emailAddresses)')
            ->setParameter('emailAddresses', $emailAddresses)
            ->andWhere('u.isEnabled = :reverseToggle')
            ->setParameter('reverseToggle', $enabled)
            ->getQuery()
            ->getResult();
    }
}
