<?php

namespace App\Repository;

use App\Entity\ActionLogChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActionLogChange|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionLogChange|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionLogChange[]    findAll()
 * @method ActionLogChange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionLogChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionLogChange::class);
    }
}
