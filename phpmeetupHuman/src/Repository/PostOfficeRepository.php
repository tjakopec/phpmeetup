<?php

namespace App\Repository;

use App\Entity\PostOffice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostOffice>
 */
class PostOfficeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostOffice::class);
    }

    /**
     * Finds the corresponding delivery zone based on the office's zip code.
     */
    public function findOneByPostalCode(string $postalCode): ?PostOffice
    {
        /* @var PostOffice|null */
        return $this->findOneBy(['postalCode' => $postalCode]);
    }
}
