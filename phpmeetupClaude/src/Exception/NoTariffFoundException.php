<?php

declare(strict_types=1);

namespace App\Exception;

final class NoTariffFoundException extends \RuntimeException
{
    public function __construct(int $zoneId, float $weight)
    {
        parent::__construct(
            sprintf(
                'No tariff bracket found for zone ID %d and chargeable weight %.3f kg. Please check the tariff configuration.',
                $zoneId,
                $weight,
            ),
        );
    }
}
