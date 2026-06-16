<?php

namespace App\Repository;

use App\Entity\ServiceType;
use App\Entity\Tariff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tariff>
 */
class TariffRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tariff::class);
    }

    /**
     * Finds the base price (basePrice) based on the service and weight of the package.
     *
     * @return float|null Returns the price or null if no tariff exists for that weight
     */
    public function findPriceByServiceAndWeight(ServiceType $service, float $weight): ?float
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.basePrice')
            ->where('t.serviceType = :service')
            ->andWhere(':weight >= t.minWeight')
            ->andWhere(':weight < t.maxWeight')
            ->setParameter('service', $service)
            ->setParameter('weight', $weight)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);

        if (null !== $result && is_scalar($result)) {
            return (float) $result;
        }

        return null;
    }
}
