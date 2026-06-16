<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\ShippingConfiguration;
use App\Dto\PriceBreakdown;
use App\Dto\ShippingQuoteInput;
use App\Dto\ShippingQuoteOutput;
use App\Exception\NoTariffFoundException;
use App\Exception\OversizedParcelException;
use App\Exception\UnresolvablePostalCodeException;
use App\Repository\PostalCodeRepositoryInterface;
use App\Repository\WeightTariffRepositoryInterface;

final class ShippingPriceCalculator implements ShippingPriceCalculatorInterface
{
    public function __construct(
        private readonly PostalCodeRepositoryInterface $postalCodeRepository,
        private readonly WeightTariffRepositoryInterface $tariffRepository,
        private readonly ShippingConfiguration $config,
    ) {
    }

    public function calculate(ShippingQuoteInput $input): ShippingQuoteOutput
    {
        // a) Resolve postal code → ShippingZone
        $postalCode = $this->postalCodeRepository->findByCode($input->postalCode);
        if ($postalCode === null) {
            throw new UnresolvablePostalCodeException($input->postalCode);
        }
        $zone = $postalCode->getZone();

        // b) Validate hard limits BEFORE pricing
        $maxWeight = $this->config->getMaxWeightForService($input->serviceType);
        if ($input->weight > $maxWeight) {
            throw new OversizedParcelException(
                sprintf(
                    'Weight %.3f kg exceeds the maximum of %.0f kg for "%s" service.',
                    $input->weight,
                    $maxWeight,
                    $input->serviceType,
                ),
            );
        }

        $maxDimension = $this->config->getMaxDimensionForService($input->serviceType);
        $longestSide = max($input->dimensions->length, $input->dimensions->width, $input->dimensions->height);
        if ($longestSide > $maxDimension) {
            throw new OversizedParcelException(
                sprintf(
                    'Longest dimension %.1f cm exceeds the maximum of %.0f cm for "%s" service.',
                    $longestSide,
                    $maxDimension,
                    $input->serviceType,
                ),
            );
        }

        // c) Compute volumetric weight and chargeable weight
        $volumetricWeight = ($input->dimensions->length * $input->dimensions->width * $input->dimensions->height)
            / $this->config->getVolumetricDivisor();
        $chargeableWeight = max($input->weight, $volumetricWeight);

        // d) Find tariff bracket
        $zoneId = $zone->getId();
        if ($zoneId === null) {
            throw new \LogicException('Zone ID must not be null when calculating shipping price.');
        }

        $tariff = $this->tariffRepository->findBracketForZoneAndWeight($zoneId, $chargeableWeight);
        if ($tariff === null) {
            throw new NoTariffFoundException($zoneId, $chargeableWeight);
        }

        // e) Compute base price from bracket
        $base = $tariff->getBasePrice()
            + ($chargeableWeight - $tariff->getMinWeight()) * $tariff->getWeightUnitPrice();

        // f) Weight surcharge: always 0.00 (base already covers weight tiers)
        $weightSurcharge = 0.00;

        // g) Dimensional surcharge: only when volumetric > actual weight
        $dimensionalSurcharge = 0.00;
        if ($volumetricWeight > $input->weight) {
            $dimensionalSurcharge = round(
                ($volumetricWeight - $input->weight) * $tariff->getWeightUnitPrice(),
                2,
            );
        }

        // h) Zone surcharge
        $zoneSurcharge = $zone->getZoneSurcharge();

        // i) Priority multiplier
        $priorityMultiplier = $input->serviceType === 'priority'
            ? $this->config->getPriorityMultiplier()
            : 1.0;

        // j) Final price
        $subtotal = $base + $dimensionalSurcharge + $zoneSurcharge;
        $price = round($subtotal * $priorityMultiplier, 2);

        // k) Estimated delivery days
        $deliveryDays = $input->serviceType === 'priority'
            ? $this->config->getPriorityDeliveryDaysBase()
            : $this->config->getRegularDeliveryDaysBase();

        // l) Build and return output
        return new ShippingQuoteOutput(
            price: $price,
            currency: 'EUR',
            serviceType: $input->serviceType,
            estimatedDeliveryDays: $deliveryDays,
            breakdown: new PriceBreakdown(
                base: round($base, 2),
                weightSurcharge: $weightSurcharge,
                dimensionalSurcharge: $dimensionalSurcharge,
                zoneSurcharge: $zoneSurcharge,
                priorityMultiplier: $priorityMultiplier,
            ),
        );
    }
}
