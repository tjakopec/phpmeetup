<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Dto\ParcelDimensions;
use App\Dto\PriceBreakdown;
use App\Dto\ShippingQuoteRequest;
use App\Dto\ShippingQuoteResponse;
use App\Dto\VolumeCalculator;
use App\Enums\ServiceType;
use App\Exceptions\DomainException;
use App\Exceptions\ValidationException;
use App\Repositories\SettingsRepository;
use App\Repositories\TariffRepository;
use App\Repositories\ZoneRepository;

final class ShippingCalculator
{
    public function __construct(
        private readonly TariffRepository $tariffRepo,
        private readonly ZoneRepository $zoneRepo,
        private readonly SettingsRepository $settingsRepo
    ) {
    }

    /** Calculate shipping quote for a request */
    public function calculate(ShippingQuoteRequest $request): ShippingQuoteResponse
    {
        $this->validateRequest($request);

        // Get charged weight (actual or volumetric, whichever is greater)
        $chargedWeight = $this->calculateChargedWeight($request);

        // Calculate price breakdown using the default service type
        $serviceType = $request->getServiceType() ?? ServiceType::REGULAR;

        $basePrice = $this->tariffRepo->getBasePrice($serviceType, $chargedWeight);
        $currency = $this->tariffRepo->getCurrency($serviceType);
        $vatPercentage = $this->settingsRepo->getVatPercentage();

        $priceBreakdown = $this->buildPriceBreakdown(
            $basePrice,
            $serviceType,
            $chargedWeight,
            $request->getDestinationZone(),
            $currency,
            $vatPercentage
        );

        // Round totals if enabled
        if ($this->settingsRepo->isRoundingEnabled()) {
            $precision = $this->settingsRepo->getRoundingPrecision();
            $priceBreakdown = new PriceBreakdown(
                round($priceBreakdown->getFuelSurcharge(), $precision),
                round($priceBreakdown->getZoneSurcharge(), $precision),
                round($priceBreakdown->getSubtotal(), $precision),
                round($priceBreakdown->getVatAmount(), $precision)
            );
        }

        return new ShippingQuoteResponse(
            $serviceType,
            $priceBreakdown,
            $chargedWeight,
            $currency,
            true // VAT inclusive by default
        );
    }

    /** Validate the shipping request */
    private function validateRequest(ShippingQuoteRequest $request): void
    {
        $errors = [];

        // Validate dimensions exist
        if ($request->getDimensions() !== null) {
            $dimensions = $request->getDimensions();
            if ($dimensions->getWidthCm() <= 0 || $dimensions->getHeightCm() <= 0 || $dimensions->getLengthCm() <= 0) {
                $errors[] = 'Dimensions must be greater than 0';
            }

            // Check single dimension limit (200cm per Plan.txt section 4.1)
            $maxSingleDimension = 200;
            if ($dimensions->getWidthCm() > $maxSingleDimension ||
                $dimensions->getHeightCm() > $maxSingleDimension ||
                $dimensions->getLengthCm() > $maxSingleDimension) {
                $errors[] = 'Single dimension exceeds maximum allowed (200cm)';
            }

            // Check max weight
            $maxWeight = 50;
            if ($request->getWeightKg() > $maxWeight) {
                $errors[] = "Weight exceeds maximum allowed ({$maxWeight}kg)";
            }

            // Check minimum weight
            if ($request->getWeightKg() <= 0) {
                $errors[] = 'Weight must be greater than 0';
            }
        }

        // Validate destination zone
        $destinationZone = $request->getDestinationZone();
        if (!$this->zoneRepo->isValidZone($destinationZone)) {
            $validZones = implode(', ', $this->zoneRepo->getAllZoneCodes());
            throw new ValidationException("Invalid destination zone: {$destinationZone}. Valid zones: {$validZones}");
        }

        // Validate service type
        if ($request->getServiceType() === null) {
            // Default to REGULAR is fine
        }

        if (!empty($errors)) {
            throw new ValidationException(implode('; ', $errors));
        }
    }

    /** Calculate the charged weight (actual vs volumetric) */
    private function calculateChargedWeight(ShippingQuoteRequest $request): float
    {
        // If no dimensions provided, use actual weight as charged weight
        if ($request->getDimensions() === null) {
            return $request->getWeightKg();
        }

        $dimensions = $request->getDimensions();
        $volumetricWeight = VolumeCalculator::calculate(
            $dimensions->getWidthCm(),
            $dimensions->getHeightCm(),
            $dimensions->getLengthCm(),
            $this->settingsRepo->getDefaultDivisor()
        );

        return round(max($request->getWeightKg(), $volumetricWeight), 2);
    }

    /** Build the price breakdown for the shipping quote */
    private function buildPriceBreakdown(
        float $basePrice,
        ServiceType $serviceType,
        float $chargedWeight,
        string $destinationZone,
        string $currency,
        float $vatPercentage
    ): PriceBreakdown {
        // Apply priority multiplier (1.5x for Priority)
        if ($serviceType === ServiceType::PRIORITY) {
            $basePrice = round($basePrice * 1.5, 2);
        }

        // Zone surcharge (percentage from zone config)
        $zoneSurchargePercent = $this->zoneRepo->getZoneSurcharge($destinationZone);
        $fuelSurchargeAmount = round($basePrice * 0.10, 2); // 10% fuel surcharge
        $zoneSurchargeAmount = round($basePrice * ($zoneSurchargePercent / 100), 2);

        // Subtotal before VAT
        $subtotal = round($basePrice + $fuelSurchargeAmount + $zoneSurchargeAmount, 2);

        // VAT calculation
        $vatAmount = round($subtotal * ($vatPercentage / 100), 2);

        return new PriceBreakdown(
            $fuelSurchargeAmount,
            $zoneSurchargeAmount,
            $subtotal,
            $vatAmount
        );
    }
}