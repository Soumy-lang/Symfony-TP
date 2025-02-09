<?php

namespace App\Repository;

use App\Entity\Vehicle;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function findByFilters($marque = null, $disponible = null, $prixMin = null, $prixMax = null)
    {
        $qb = $this->createQueryBuilder('v');

        if ($marque) {
            $qb->andWhere('v.marque LIKE :marque')
               ->setParameter('marque', '%' . $marque . '%');
        }

        if ($disponible !== null) {
            $qb->andWhere('v.disponible = :disponible')
               ->setParameter('disponible', $disponible);
        }

        if ($prixMin) {
            $qb->andWhere('v.prixJournalier >= :prixMin')
               ->setParameter('prixMin', $prixMin);
        }

        if ($prixMax) {
            $qb->andWhere('v.prixJournalier <= :prixMax')
               ->setParameter('prixMax', $prixMax);
        }

        return $qb->getQuery()->getResult();
    }

    public function getAverageRating(int $vehicleId): ?float
    {
        return $this->createQueryBuilder('v')
            ->select('AVG(c.rating) as avgRating')
            ->leftJoin('v.comment', 'c')
            ->where('v.id = :vehicleId')
            ->setParameter('vehicleId', $vehicleId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findWithImages(int $id): ?Vehicle
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.vehicleImage', 'i')
            ->addSelect('i')
            ->where('v.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
