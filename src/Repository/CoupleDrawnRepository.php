<?php

namespace App\Repository;

use App\Entity\CoupleDrawn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoupleDrawn>
 *
 * @method CoupleDrawn|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoupleDrawn|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoupleDrawn[]    findAll()
 * @method CoupleDrawn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoupleDrawnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoupleDrawn::class);
    }

    //    /**
    //     * @return CoupleDrawn[] Returns an array of CoupleDrawn objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CoupleDrawn
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
