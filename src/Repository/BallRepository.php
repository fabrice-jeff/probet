<?php

namespace App\Repository;

use App\Entity\Ball;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ball>
 *
 * @method Ball|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ball|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ball[]    findAll()
 * @method Ball[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ball::class);
    }

    //    /**
    //     * @return Ball[] Returns an array of Ball objects
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

    //    public function findOneBySomeField($value): ?Ball
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
