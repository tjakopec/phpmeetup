<?php

namespace App\Service;

use App\Dto\PriceBreakdown;
use App\Dto\QuoteRequest;
use App\Dto\QuoteResponse;
use App\Entity\PostOffice;
use App\Entity\ServiceType;
use App\Entity\ShippingZone;
use App\Entity\Tariff;
use App\Repository\PostOfficeRepository;
use App\Repository\ServiceTypeRepository;
use App\Repository\ShippingZoneRepository;
use App\Repository\TariffRepository;

class ShippingPriceCalculator implements ShippingPriceCalculatorInterface
{
    public function __construct(
        private ShippingZoneRepository $zoneRepository,
        private ServiceTypeRepository $serviceTypeRepository,
        private PostOfficeRepository $postOfficeRepository,
        private TariffRepository $tariffRepository,
        private RequestDataStore $dataStore,
    ) {
    }

    public function calculate(QuoteRequest $request): QuoteResponse
    {
        $hasQuoteData = $this->dataStore->get('quote_data') === true;

        $serviceType = $hasQuoteData
            ? $this->dataStore->get('quote_service_type')
            : $this->serviceTypeRepository->findOneBy(['code' => $request->serviceType]);

        if (!$serviceType instanceof ServiceType) {
            throw new \RuntimeException('Service type not found.');
        }

        $zone = $hasQuoteData
            ? $this->dataStore->get('quote_shipping_zone')
            : $this->zoneRepository->findOneByPostalCode($request->postalCode);

        if (!$zone instanceof ShippingZone) {
            throw new \RuntimeException('Shipping zone not found.');
        }

        $postOffice = $hasQuoteData
            ? $this->dataStore->get('quote_post_office')
            : $this->postOfficeRepository->findOneByPostalCode($request->postalCode);

        if (!$postOffice instanceof PostOffice) {
            throw new \RuntimeException('Post office not found.');
        }

        $currentTariff = $this->dataStore->get('quote_current_tariff');

        $basePriceRaw = ($currentTariff instanceof Tariff)
            ? $currentTariff->getBasePrice()
            : $this->tariffRepository->findPriceByServiceAndWeight($serviceType, 0.5);

        $basePrice = $basePriceRaw ?? 0.00;

        $actualTariffPriceRaw = $hasQuoteData
            ? $this->dataStore->get('quote_actual_tariff_price')
            : $this->tariffRepository->findPriceByServiceAndWeight($serviceType, $request->weight);

        $actualTariffPrice = (null !== $actualTariffPriceRaw && is_scalar($actualTariffPriceRaw))
            ? (float) $actualTariffPriceRaw
            : null;

        $actualWeightPrice = $actualTariffPrice ?? $basePrice;

        $computedWeightSurcharge = max(0.00, $actualWeightPrice - $basePrice);

        $divisor = $serviceType->getVolumeDivisor();
        $dimensions = $request->getDimensions();
        $volumetricWeight = ($dimensions->length * $dimensions->width * $dimensions->height) / ($divisor > 0 ? $divisor : 1);

        $computedDimensionalSurcharge = 0.00;
        if ($volumetricWeight > $request->weight) {
            $weightDifference = $volumetricWeight - $request->weight;
            $dimensionalSurcharge = $serviceType->getDimensionalSurcharge();
            $computedDimensionalSurcharge = $weightDifference * $dimensionalSurcharge;
        }

        $priorityMultiplier = $serviceType->getPriorityMultiplier();

        $breakdown = new PriceBreakdown(
            $basePrice,
            (float) number_format($computedWeightSurcharge, 2, '.', ''),
            $computedDimensionalSurcharge,
            $zone->getZoneSurcharge(),
            $priorityMultiplier
        );

        $subTotal = $basePrice + $computedWeightSurcharge + $computedDimensionalSurcharge + $zone->getZoneSurcharge();
        $totalPrice = $subTotal * $priorityMultiplier;

        $estimatedDeliveryDays = max(1,
            $zone->getBaseDeliveryDays() - $serviceType->getReducesEstimatedDeliveryDays());

        $currencyRaw = $postOffice->getCurrency();
        $currency = $currencyRaw ?? 'EUR';

        $serviceCode = $serviceType->getCode();

        return new QuoteResponse(
            $totalPrice,
            $currency,
            $serviceCode,
            $estimatedDeliveryDays,
            $breakdown
        );
    }
}
