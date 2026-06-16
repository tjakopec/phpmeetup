<?php

declare(strict_types=1);

namespace App\Dto;

final class PriceBreakdown
{
    public function __construct(
        private readonly float $basePrice,
        private readonly float $zoneSurcharge,
        private readonly ?float $oversizeFee
    ) {
    }

    public function getBasePrice(): float
    {
        return $this->basePrice;
    }

    public function getZoneSurcharge(): float
    {
        return $this->zoneSurcharge;
    }

    public function getOversizeFee(): ?float
    {
        return $this->oversizeFee;
    }

    public function getSubtotal(): float
    {
        $subtotal = $this->basePrice + $this->zoneSurcharge;
        if ($this->oversizeFee !== null) {
            $subtotal += $this->oversizeFee;
        }
        return $subtotal;
    }

    /** @return array<string, float|null> */
    public function toArray(): array
    {
        return [
            'base_price' => round($this->basePrice, 2),
            'zone_surcharge' => round($this->zoneSurcharge, 2),
            'oversize_fee' => $this->oversizeFee !== null ? round($this->oversizeFee, 2) : null,
        ];
    }
}