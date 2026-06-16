<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WeightTariff;

interface WeightTariffRepositoryInterface
{
    public function findBracketForZoneAndWeight(int $zoneId, float $weight): ?WeightTariff;
}
