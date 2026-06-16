<?php

declare(strict_types=1);

namespace App\Dto;

final class PriceBreakdown
{
    public function __construct(
        public readonly float $base,
        public readonly float $weightSurcharge,
        public readonly float $dimensionalSurcharge,
        public readonly float $zoneSurcharge,
        public readonly float $priorityMultiplier,
    ) {
    }
}
