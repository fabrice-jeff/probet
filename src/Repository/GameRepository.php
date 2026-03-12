<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\Game;
use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 *
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * @return Game[] Returns an array of Game objects
     */
    public function findGamesOfDay(): array
    {
        $now = new \DateTime('now');
        $date = $now->format('Y-m-d');
        $startOfDay = new \DateTime($date . ' 00:00:00');
        $endOfDay = new \DateTime($date . ' 23:59:59');
        return $this->createQueryBuilder('g')
            ->where('g.deleted = :deleted')
            ->andWhere('g.createdAt BETWEEN :start AND :end')
            ->setParameter('deleted', false)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getResult();
    }
    public function findOneGameOfDay(\DateTime $dateTime, Status $status, Country $country): Game
    {
        $date = $dateTime->format('Y-m-d');
        $startOfDay = new \DateTime($date . ' 00:00:00');
        $endOfDay = new \DateTime($date . ' 23:59:59');
        $game =  $this->createQueryBuilder('g')
            ->where('g.deleted = :deleted')
            ->andWhere('g.createdAt BETWEEN :start AND :end')
            ->andWhere('g.status = :status ')
            ->setParameter('deleted', false)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('status',  $status)
            ->getQuery()
            ->getOneOrNullResult();

        if ($game === null) {
            throw new \Exception('No game found for the given day and status');
        }
        return $game;
    }

    //    public function findOneBySomeField($value): ?Game
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
