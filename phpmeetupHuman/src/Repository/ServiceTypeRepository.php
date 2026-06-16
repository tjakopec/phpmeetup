<?php

namespace App\Repository;

use App\Entity\ServiceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceType>
 */
class ServiceTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceType::class);
    }

    // BITNO
    // maxWeight i maxDimension mi vjerorajno ne trebaju!!!!!!
    // prouči i promjeni

    /**
     * Returns the maximum allowed weight among all service types.
     */
    public function findAbsoluteMaxWeight(): ?float
    {
        $result = $this->createQueryBuilder('s')
            ->select('MAX(s.maxWeight)')
            ->getQuery()
            ->getSingleScalarResult();

        return null !== $result ? (float) $result : null;
    }
}
