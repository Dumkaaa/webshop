<?php

namespace App\Repository;

use App\Entity\ActionLog;
use App\Entity\Admin\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActionLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionLog[]    findAll()
 * @method ActionLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionLog::class);
    }

    /**
     * Find all action logs for the given user in this period grouped by the action, user, object class & createdAt.
     * This way bulk actions won't be seen as loads of different actions and distort potential data.
     *
     * @return array<ActionLog>
     */
    public function findGroupedForUserBetween(User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('a')
            ->where('IDENTITY(a.user) = :userId')
            ->andWhere('a.createdAt >= :startDate')
            ->andWhere('a.createdAt <= :endDate')
            ->setParameters([
                'userId' => $user->getId(),
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->groupBy('a.action, a.user, a.objectClass, a.createdAt')
            ->orderBy('a.createdAt')
            ->getQuery()
            ->getResult();
    }
}
