<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ServiceType;
use App\Exceptions\DomainException;
use App\Parsers\TariffParser;

final class TariffRepository
{
    /** @var array<string, array<string, mixed>> */
    private array $tariffs = [];

    public function __construct(
        private readonly string $tariffsDirectory
    ) {
    }

    /** Get the base price for a service type and weight */
    public function getBasePrice(ServiceType $serviceType, float $weightKg): float
    {
        $tariff = $this->loadTariff($serviceType);
        $basePrice = self::calculateWeightedPrice($tariff['weight_tiers'], $weightKg);

        if ($basePrice === null) {
            throw new DomainException(
                "No tariff found for service: {$serviceType->value}, weight: {$weightKg}kg"
            );
        }

        return (float)$basePrice;
    }

    /** Get the currency from the tariff */
    public function getCurrency(ServiceType $serviceType): string
    {
        $tariff = $this->loadTariff($serviceType);
        return (string)$tariff['currency'];
    }

    /** Check if a weight is within allowed limits for a service type */
    public function isWeightAllowed(ServiceType $serviceType, float $weightKg): bool
    {
        $tariff = $this->loadTariff($serviceType);
        $maxWeight = $tariff['max_weight_kg'] ?? 30;
        return $weightKg <= $maxWeight && $weightKg > 0;
    }

    /** @return array<string, mixed> */
    private function loadTariff(ServiceType $serviceType): array
    {
        $fileName = $serviceType === ServiceType::REGULAR
            ? 'tariffs_regular.json'
            : 'tariffs_priority.json';

        if (isset($this->tariffs[$fileName])) {
            return $this->tariffs[$fileName];
        }

        $filePath = "{$this->tariffsDirectory}/{$fileName}";
        $this->tariffs[$fileName] = TariffParser::parseFile($filePath);

        return $this->tariffs[$fileName];
    }

    /** Calculate weighted price from weight tiers */
    private static function calculateWeightedPrice(array $weightTiers, float $weightKg): ?float
    {
        // Sort tiers by max_weight ascending
        usort($weightTiers, function ($a, $b) {
            return $a['max_weight_kg'] <=> $b['max_weight_kg'];
        });

        foreach ($weightTiers as $tier) {
            if ($weightKg <= $tier['max_weight_kg']) {
                return $tier['price'];
            }
        }

        // Weight exceeds all tiers - return the max price
        $lastTier = end($weightTiers);
        return $lastTier !== false ? $lastTier['price'] : null;
    }

    /** Get max weight for a service type */
    public function getMaxWeight(ServiceType $serviceType): float
    {
        $tariff = $this->loadTariff($serviceType);
        return (float)($tariff['max_weight_kg'] ?? 30);
    }
}