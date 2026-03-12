<?php

namespace App\Repository;

use App\Entity\BallWinnerDrawn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BallWinnerDrawn>
 *
 * @method BallWinnerDrawn|null find($id, $lockMode = null, $lockVersion = null)
 * @method BallWinnerDrawn|null findOneBy(array $criteria, array $orderBy = null)
 * @method BallWinnerDrawn[]    findAll()
 * @method BallWinnerDrawn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BallWinnerDrawnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BallWinnerDrawn::class);
    }

//    /**
//     * @return BallWinnerDrawn[] Returns an array of BallWinnerDrawn objects
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

//    public function findOneBySomeField($value): ?BallWinnerDrawn
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
