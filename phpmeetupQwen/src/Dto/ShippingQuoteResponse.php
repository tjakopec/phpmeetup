<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\ServiceType;

final class ShippingQuoteResponse
{
    public function __construct(
        private readonly ServiceType $serviceType,
        private readonly PriceBreakdown $priceBreakdown,
        private readonly float $chargedWeightKg,
        private readonly string $currency,
        private readonly bool $isVatInclusive
    ) {
    }

    public function getServiceType(): ServiceType
    {
        return $this->serviceType;
    }

    public function getPriceBreakdown(): PriceBreakdown
    {
        return $this->priceBreakdown;
    }

    public function getChargedWeightKg(): float
    {
        return $this->chargedWeightKg;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function isVatInclusive(): bool
    {
        return $this->isVatInclusive;
    }

    /** Total price including all fees */
    public function getTotalPrice(): float
    {
        $subtotal = $this->priceBreakdown->getSubtotal();
        if ($this->serviceType === ServiceType::PRIORITY) {
            $subtotal *= 1.5;
        }
        return round($subtotal, 2);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $totalPrice = $this->getTotalPrice();
        $response = [
            'service_type' => $this->serviceType->value,
            'charged_weight_kg' => round($this->chargedWeightKg, 2),
            'currency' => $this->currency,
            'price_breakdown' => $this->priceBreakdown->toArray(),
            'total_price' => $totalPrice,
        ];

        if ($this->serviceType === ServiceType::PRIORITY) {
            $response['priority_multiplier'] = 1.5;
        }

        return $response;
    }
}