<?php

declare(strict_types=1);

namespace App\Dto;

final class ShippingQuoteOutput
{
    public function __construct(
        public readonly float $price,
        public readonly string $currency,
        public readonly string $serviceType,
        public readonly int $estimatedDeliveryDays,
        public readonly PriceBreakdown $breakdown,
    ) {
    }
}
