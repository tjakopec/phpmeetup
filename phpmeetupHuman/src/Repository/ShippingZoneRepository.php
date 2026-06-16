<?php

namespace App\Repository;

use App\Entity\ShippingZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShippingZone>
 */
class ShippingZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingZone::class);
    }

    /**
     * Finds the corresponding delivery zone based on the office's zip code.
     */
    public function findOneByPostalCode(string $postalCode): ?ShippingZone
    {
        /** @var ShippingZone|null $result */
        $result = $this->createQueryBuilder('sz')
            ->innerJoin('App\Entity\PostOffice', 'po', 'WITH', 'po.shippingZone = sz')
            ->where('po.postalCode = :postalCode')
            ->setParameter('postalCode', $postalCode)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
