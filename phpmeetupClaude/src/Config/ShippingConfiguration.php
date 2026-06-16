<?php

declare(strict_types=1);

namespace App\Config;

final class ShippingConfiguration
{
    public function __construct(
        private readonly int $volumetricDivisor,
        private readonly float $priorityMultiplier,
        private readonly int $regularDeliveryDaysBase,
        private readonly int $priorityDeliveryDaysBase,
        private readonly array $maxWeight,
        private readonly array $maxDimension,
    ) {
    }

    public function getVolumetricDivisor(): int
    {
        return $this->volumetricDivisor;
    }

    public function getPriorityMultiplier(): float
    {
        return $this->priorityMultiplier;
    }

    public function getRegularDeliveryDaysBase(): int
    {
        return $this->regularDeliveryDaysBase;
    }

    public function getPriorityDeliveryDaysBase(): int
    {
        return $this->priorityDeliveryDaysBase;
    }

    public function getMaxWeightForService(string $serviceType): float
    {
        return (float) ($this->maxWeight[$serviceType] ?? $this->maxWeight['regular']);
    }

    public function getMaxDimensionForService(string $serviceType): float
    {
        return (float) ($this->maxDimension[$serviceType] ?? $this->maxDimension['regular']);
    }
}
