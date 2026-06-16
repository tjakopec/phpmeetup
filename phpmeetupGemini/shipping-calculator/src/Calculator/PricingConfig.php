<?php

namespace App\Calculator;

class PricingConfig
{
    public float $volumetricDivisor = 5000.0;

    /**
     * @var array<string, array{min: float, max: float, price: float}>
     */
    public array $weightTiers = [
        'tier1' => ['min' => 0.0, 'max' => 2.0, 'price' => 5.0],
        'tier2' => ['min' => 2.0, 'max' => 5.0, 'price' => 8.0],
        'tier3' => ['min' => 5.0, 'max' => 10.0, 'price' => 12.0],
        'tier4' => ['min' => 10.0, 'max' => 20.0, 'price' => 18.0],
    ];

    /**
     * @var array<string, float>
     */
    public array $zoneSurcharges = [
        'mainland' => 0.0,
        'islands'  => 5.0,
        'remote'   => 10.0,
    ];

    public float $priorityMultiplier = 1.5;

    public function resolveZone(string $postalCode): string
    {
        // simplistic logic based on ranges
        $prefix = (int) substr($postalCode, 0, 2);
        if ($prefix === 21 || $prefix === 20) {
            return 'islands';
        }
        if ($prefix === 51 || $prefix === 53) {
            return 'remote';
        }
        return 'mainland';
    }

    public function getBasePrice(float $weight): float
    {
        foreach ($this->weightTiers as $tier) {
            if ($weight > $tier['min'] && $weight <= $tier['max']) {
                return $tier['price'];
            }
        }
        // if exceeds max tier, calculate an overflow price or return max
        return 25.0; // fallback max
    }
}
