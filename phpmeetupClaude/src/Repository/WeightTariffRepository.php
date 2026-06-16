<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WeightTariff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeightTariff>
 */
class WeightTariffRepository extends ServiceEntityRepository implements WeightTariffRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeightTariff::class);
    }

    public function findBracketForZoneAndWeight(int $zoneId, float $weight): ?WeightTariff
    {
        return $this->createQueryBuilder('wt')
            ->where('wt.zone = :zoneId')
            ->andWhere('wt.minWeight <= :weight')
            ->andWhere('wt.maxWeight IS NULL OR wt.maxWeight >= :weight')
            ->setParameter('zoneId', $zoneId)
            ->setParameter('weight', $weight)
            ->orderBy('wt.minWeight', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
