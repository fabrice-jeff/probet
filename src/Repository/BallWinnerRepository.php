<?php

namespace App\Repository;

use App\Entity\BallWinner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BallWinner>
 *
 * @method BallWinner|null find($id, $lockMode = null, $lockVersion = null)
 * @method BallWinner|null findOneBy(array $criteria, array $orderBy = null)
 * @method BallWinner[]    findAll()
 * @method BallWinner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BallWinnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BallWinner::class);
    }

    //    /**
    //     * @return BallWinner[] Returns an array of BallWinner objects
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

    //    public function findOneBySomeField($value): ?BallWinner
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
