<?php

namespace App\EventListener;

use App\Entity\PostOffice;
use App\Entity\ServiceType;
use App\Entity\ShippingZone;
use App\Entity\Tariff;
use App\Repository\ShippingPriceAllInOneRepository;
use App\Service\RequestDataStore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestDataListener implements EventSubscriberInterface
{
    public function __construct(
        private RequestDataStore $dataStore,
        private ShippingPriceAllInOneRepository $allInOneRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->dataStore->set('quote_data', false);

        $request = $event->getRequest();

        /** @var array<string, mixed> $content */
        $content = json_decode($request->getContent(), true) ?? [];

        $optimised = isset($content['optimised']) && is_bool($content['optimised']) ? (bool) $content['optimised'] : false;
        
        if(!$optimised) {
            return;
        }
        $postalCode = isset($content['postalCode']) && is_scalar($content['postalCode']) ? (string) $content['postalCode'] : '';
        $serviceTypeStr = isset($content['serviceType']) && is_scalar($content['serviceType']) ? (string) $content['serviceType'] : 'regular';
        $baseWeight = 0.5;
        $weight = isset($content['weight']) && is_numeric($content['weight']) ? (float) $content['weight'] : 0.5;

        if ($postalCode === '') {
            return;
        }

        /** @var array<array<string, mixed>>|null $rawRows */
        $rawRows = $this->allInOneRepository->getAllData($postalCode, $serviceTypeStr, $baseWeight, $weight);

        if (null === $rawRows) {
            return;
        }

        $shippingZone = null;
        $postOffice = null;
        $serviceType = null;
        $tariff = null;

        foreach ($rawRows as $row) {
            $tipReda = isset($row['row_type']) && is_scalar($row['row_type']) ? (string) $row['row_type'] : '';

            if ($tipReda === 'location_zone') {
                $zoneId = isset($row['kol_int_2']) && is_numeric($row['kol_int_2']) ? (int) $row['kol_int_2'] : 0;
                $zoneName = isset($row['kol_vchar_4']) && is_scalar($row['kol_vchar_4']) ? (string) $row['kol_vchar_4'] : '';
                $deliveryDays = isset($row['kol_int_3']) && is_numeric($row['kol_int_3']) ? (int) $row['kol_int_3'] : 0;
                $zoneSurcharge = isset($row['kol_double_1']) && is_numeric($row['kol_double_1']) ? (float) $row['kol_double_1'] : 0.0;

                $officeId = isset($row['kol_int_1']) && is_numeric($row['kol_int_1']) ? (int) $row['kol_int_1'] : 0;
                $officePostalCode = isset($row['kol_vchar_1']) && is_scalar($row['kol_vchar_1']) ? (string) $row['kol_vchar_1'] : '';
                $officeName = isset($row['kol_vchar_2']) && is_scalar($row['kol_vchar_2']) ? (string) $row['kol_vchar_2'] : '';
                $officeCurrency = isset($row['kol_vchar_3']) && is_scalar($row['kol_vchar_3']) ? (string) $row['kol_vchar_3'] : 'EUR';

                $shippingZone = new ShippingZone();
                $shippingZone->setId($zoneId);
                $shippingZone->setName($zoneName);
                $shippingZone->setBaseDeliveryDays($deliveryDays);
                $shippingZone->setZoneSurcharge($zoneSurcharge);

                $postOffice = new PostOffice();
                $postOffice->setId($officeId);
                $postOffice->setPostalCode($officePostalCode);
                $postOffice->setName($officeName);
                $postOffice->setCurrency($officeCurrency);

                $postOffice->setShippingZone($shippingZone);
                $shippingZone->addPostOffice($postOffice);
            }

            if ($tipReda === 'service_tariff') {
                $serviceId = isset($row['kol_int_1']) && is_numeric($row['kol_int_1']) ? (int) $row['kol_int_1'] : 0;
                $serviceCode = isset($row['kol_vchar_1']) && is_scalar($row['kol_vchar_1']) ? (string) $row['kol_vchar_1'] : '';
                $serviceName = isset($row['kol_vchar_2']) && is_scalar($row['kol_vchar_2']) ? (string) $row['kol_vchar_2'] : '';

                $volDivisor = isset($row['kol_int_3']) && is_numeric($row['kol_int_3']) ? (int) $row['kol_int_3'] : 0;
                $redDays = isset($row['kol_int_4']) && is_numeric($row['kol_int_4']) ? (int) $row['kol_int_4'] : 0;

                $wSurcharge = isset($row['kol_double_1']) && is_numeric($row['kol_double_1']) ? (float) $row['kol_double_1'] : 0.0;
                $dSurcharge = isset($row['kol_double_2']) && is_numeric($row['kol_double_2']) ? (float) $row['kol_double_2'] : 0.0;
                $pMultiplier = isset($row['kol_double_3']) && is_numeric($row['kol_double_3']) ? (float) $row['kol_double_3'] : 1.0;
                $maxW = isset($row['kol_double_5']) && is_numeric($row['kol_double_5']) ? (float) $row['kol_double_5'] : 0.0;
                $maxD = isset($row['kol_double_6']) && is_numeric($row['kol_double_6']) ? (float) $row['kol_double_6'] : 0.0;

                $tariffId = isset($row['kol_int_2']) && is_numeric($row['kol_int_2']) ? (int) $row['kol_int_2'] : 0;
                $basePrice = isset($row['kol_double_7']) && is_numeric($row['kol_double_7']) ? (float) $row['kol_double_7'] : 0.0;

                $maxWeightStorage = isset($row['kol_double_8']) && is_numeric($row['kol_double_8']) ? (float) $row['kol_double_8'] : 0.0;
                $actualTariffPrice = isset($row['kol_double_9']) && is_numeric($row['kol_double_9']) ? (float) $row['kol_double_9'] : 0.0;

                $serviceType = new ServiceType();
                $serviceType->setId($serviceId);
                $serviceType->setCode($serviceCode);
                $serviceType->setName($serviceName);
                $serviceType->setVolumeDivisor($volDivisor);
                $serviceType->setReducesEstimatedDeliveryDays($redDays);
                $serviceType->setWeightSurcharge($wSurcharge);
                $serviceType->setDimensionalSurcharge($dSurcharge);
                $serviceType->setPriorityMultiplier($pMultiplier);
                $serviceType->setMaxWeight($maxW);
                $serviceType->setMaxDimension($maxD);

                $tariff = new Tariff();
                $tariff->setId($tariffId);
                $tariff->setBasePrice($basePrice);
                $tariff->setMinWeight($weight);
                $tariff->setMaxWeight($maxW);

                $tariff->setServiceType($serviceType);
                $serviceType->addTariff($tariff);

                $this->dataStore->set('quote_max_weight', $maxWeightStorage);
                $this->dataStore->set('quote_actual_tariff_price', $actualTariffPrice);
            }
        }

        if ($postOffice !== null) {
            $this->dataStore->set('quote_post_office', $postOffice);
        }

        if ($shippingZone !== null) {
            $this->dataStore->set('quote_shipping_zone', $shippingZone);
        }

        if ($serviceType !== null) {
            $this->dataStore->set('quote_service_type', $serviceType);
        }

        if ($tariff !== null) {
            $this->dataStore->set('quote_current_tariff', $tariff);
        }

        if ($postOffice !== null && $shippingZone !== null && $serviceType !== null && $tariff !== null) {
            $this->dataStore->set('quote_data', true);
        }
    }
}
