<?php

namespace App\Repository;

use App\Entity\Gol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gol>
 *
 * @method Gol|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gol|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gol[]    findAll()
 * @method Gol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gol::class);
    }

//    /**
//     * @return Gol[] Returns an array of Gol objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Gol
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
//OJO! Cantidad de goles marcados por un jugador concreto en una liga específica. Funciona. Hay que poner int en la definición de la función
//y en el return, para que devuelva un int en vez de array
public function countGoles(int $idJugador, int $idEquipo, int $idLiga): int
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT count(g.id)
               FROM App\Entity\Gol g
               JOIN App\Entity\Partido p
              WHERE g.jugador = :idJugador
                AND g.equipo = :idEquipo
                AND p.liga = :idLiga
                AND p.id = g.partido
                AND (p.local = :idEquipo OR p.visitante = :idEquipo)'
        );
        $query->setParameter('idJugador', $idJugador);
        $query->setParameter('idEquipo', $idEquipo);
        $query->setParameter('idLiga', $idLiga);

        //Devuelve un valor entero poniendo el int directamente en lugar de un array
        return (int) $query->getSingleScalarResult();
    }
    //devuelve los goles de un equipo en un partido
    public function golesEquipo(int $idEquipo, int $idPartido): int
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT count(g.id)
               FROM App\Entity\Gol g
               JOIN App\Entity\Partido p
              WHERE g.equipo = :idEquipo
                AND p.id = :idPartido
                AND p.id = g.partido'                
        );
        $query->setParameter('idEquipo', $idEquipo);
        $query->setParameter('idPartido', $idPartido);

        //Devuelve un valor entero poniendo el int directamente en lugar de un array
        return (int) $query->getSingleScalarResult();
    }
}
