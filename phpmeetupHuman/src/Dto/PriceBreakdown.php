<?php

namespace App\Dto;

readonly class PriceBreakdown
{
    public function __construct(
        public float $base,
        public float $weightSurcharge,
        public float $dimensionalSurcharge,
        public float $zoneSurcharge,
        public float $priorityMultiplier,
    ) {
    }
}
