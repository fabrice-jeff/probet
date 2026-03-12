<?php

namespace App\Repository;

use App\Entity\Bet;
use App\Utils\Constants\FixedValuesConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bet>
 *
 * @method Bet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bet[]    findAll()
 * @method Bet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bet::class);
    }
    public function findBetByPeriod(string $period): array
    {
        $now = new \DateTime('now');
        $start = null;
        $end = null;
        switch ($period) {
            case FixedValuesConstants::TYPE_PERIOD_DAY:
                $start = (clone $now)->setTime(0, 0);
                $end = (clone $now)->setTime(23, 59, 59);
                break;

            case FixedValuesConstants::TYPE_PERIOD_WEEK:
                $start = (clone $now)->modify('monday this week')->setTime(0, 0);
                $end = (clone $now)->modify('sunday this week')->setTime(23, 59, 59);
                break;

            case FixedValuesConstants::TYPE_PERIOD_MONTH:
                $start = (clone $now)->modify('first day of this month')->setTime(0, 0);
                $end = (clone $now)->modify('last day of this month')->setTime(23, 59, 59);
                break;

            case FixedValuesConstants::TYPE_PERIOD_YEAR:
                $start = (clone $now)->modify('first day of January this year')->setTime(0, 0);
                $end = (clone $now)->modify('last day of December this year')->setTime(23, 59, 59);
                break;

            default:
                throw new \InvalidArgumentException('Période invalide');
        }

        return $this->createQueryBuilder('b')
            ->where('b.deleted = :deleted')
            ->andWhere('b.createdAt BETWEEN :start AND :end')
            ->andWhere('b.status IS NOT NULL')
            ->setParameter('deleted', false)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function findBetPlay($draw): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.deleted = :deleted')
            ->andWhere('b.status IS NOT NULL')
            ->andWhere('b.draw = :draw')
            ->setParameter('deleted', false)
            ->setParameter('draw', $draw)
            ->getQuery()
            ->getResult();
    }




    //    /**
    //     * @return Bet[] Returns an array of Bet objects
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

    //    public function findOneBySomeField($value): ?Bet
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
