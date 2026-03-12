<?php

namespace App\Repository;

use App\Entity\TypeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeType>
 *
 * @method TypeType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeType[]    findAll()
 * @method TypeType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeType::class);
    }

    //    /**
    //     * @return TypeType[] Returns an array of TypeType objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TypeType
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
