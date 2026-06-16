<?php

declare(strict_types=1);

namespace App\Dto;

final class ParcelDimensions
{
    public function __construct(
        private readonly float $weightKg,
        private readonly float $lengthCm,
        private readonly float $widthCm,
        private readonly float $heightCm
    ) {
    }

    public function getWeightKg(): float
    {
        return $this->weightKg;
    }

    public function getLengthCm(): float
    {
        return $this->lengthCm;
    }

    public function getWidthCm(): float
    {
        return $this->widthCm;
    }

    public function getHeightCm(): float
    {
        return $this->heightCm;
    }

    /** @return array<string, float> */
    public function toArray(): array
    {
        return [
            'weight_kg' => $this->weightKg,
            'length_cm' => $this->lengthCm,
            'width_cm' => $this->widthCm,
            'height_cm' => $this->heightCm,
        ];
    }

    /** Get the longest side in cm */
    public function getLongestSideCm(): float
    {
        return max($this->lengthCm, $this->widthCm, $this->heightCm);
    }

    /** Get the sum of all dimensions in cm */
    public function getDimensionsSumCm(): float
    {
        return $this->lengthCm + $this->widthCm + $this->heightCm;
    }
}