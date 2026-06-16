<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PostalCode;

interface PostalCodeRepositoryInterface
{
    public function findByCode(string $code): ?PostalCode;
}
