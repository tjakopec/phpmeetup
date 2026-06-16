<?php

namespace App\Dto;

readonly class QuoteResponse
{
    public function __construct(
        public float $price,
        public string $currency,
        public string $serviceType,
        public int $estimatedDeliveryDays,
        public PriceBreakdown $breakdown,
    ) {
    }
}
