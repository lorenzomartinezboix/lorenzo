<?php

namespace App\Repository;

use App\Entity\Liga;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Liga>
 *
 * @method Liga|null find($id, $lockMode = null, $lockVersion = null)
 * @method Liga|null findOneBy(array $criteria, array $orderBy = null)
 * @method Liga[]    findAll()
 * @method Liga[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LigaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Liga::class);
    }

//    /**
//     * @return Liga[] Returns an array of Liga objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Liga
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
