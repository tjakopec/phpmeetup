<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PostalCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostalCode>
 */
class PostalCodeRepository extends ServiceEntityRepository implements PostalCodeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostalCode::class);
    }

    public function findByCode(string $code): ?PostalCode
    {
        return $this->findOneBy(['code' => $code]);
    }
}
