<?php

namespace App\Repository;

use App\Entity\BallDrawn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BallDrawn>
 *
 * @method BallDrawn|null find($id, $lockMode = null, $lockVersion = null)
 * @method BallDrawn|null findOneBy(array $criteria, array $orderBy = null)
 * @method BallDrawn[]    findAll()
 * @method BallDrawn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BallDrawnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BallDrawn::class);
    }

    //    /**
    //     * @return BallDrawn[] Returns an array of BallDrawn objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BallDrawn
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
