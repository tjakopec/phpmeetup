<?php

declare(strict_types=1);

namespace App\Dto;

final class VolumeCalculator
{
    /**
     * Calculate volumetric weight in kg from dimensions in cm.
     * Formula: (L × W × H) / divisor
     */
    public static function calculate(float $length, float $width, float $height, int $divisor = 5000): float
    {
        if ($length <= 0 || $width <= 0 || $height <= 0) {
            return 0.0;
        }

        return ($length * $width * $height) / $divisor;
    }

    /**
     * Calculate dimensional weight (charged weight) from actual and volumetric weight.
     * The charged weight is the greater of the two.
     */
    public static function calculateChargedWeight(float $actualWeightKg, float $lengthCm, float $widthCm, float $heightCm, int $divisor = 5000): float
    {
        $volumetricWeight = self::calculate($lengthCm, $widthCm, $heightCm, $divisor);
        return max($actualWeightKg, $volumetricWeight);
    }
}