<?php

namespace App\Tests\Service\PHPMeetup;

use App\Dto\DimensionsDto;
use App\Dto\QuoteRequest;
use App\Entity\PostOffice;
use App\Entity\ServiceType;
use App\Entity\ShippingZone;
use App\Repository\PostOfficeRepository;
use App\Repository\ServiceTypeRepository;
use App\Repository\ShippingZoneRepository;
use App\Repository\TariffRepository;
use App\Service\RequestDataStore;
use App\Service\ShippingPriceCalculator;
use PHPUnit\Framework\TestCase;

class PHPMeetupTaskTest extends TestCase
{
    public function testCalculateWithExactPhpMeetupData(): void
    {
        $zoneRepo = self::createStub(ShippingZoneRepository::class);
        $serviceTypeRepo = self::createStub(ServiceTypeRepository::class);
        $postOfficeRepo = self::createStub(PostOfficeRepository::class);
        $tariffRepository = self::createStub(TariffRepository::class);

        $dataStore = self::createStub(RequestDataStore::class);
        $dataStore->method('get')->willReturn(null);

        $serviceType = self::createStub(ServiceType::class);
        $serviceType->method('getCode')->willReturn('regular');
        $serviceType->method('getVolumeDivisor')->willReturn(5000);
        $serviceType->method('getDimensionalSurcharge')->willReturn(0.00);
        $serviceType->method('getPriorityMultiplier')->willReturn(1.0);
        $serviceType->method('getReducesEstimatedDeliveryDays')->willReturn(0);

        $serviceTypeRepo->method('findOneBy')->willReturn($serviceType);

        $zone = self::createStub(ShippingZone::class);
        $zone->method('getZoneSurcharge')->willReturn(1.00);
        $zone->method('getBaseDeliveryDays')->willReturn(2);

        $zoneRepo->method('findOneByPostalCode')->willReturn($zone);

        $postOffice = self::createStub(PostOffice::class);
        $postOffice->method('getCurrency')->willReturn('EUR');

        $postOfficeRepo->method('findOneByPostalCode')->willReturn($postOffice);

        $tariffRepository->method('findPriceByServiceAndWeight')
        ->willReturnCallback(function (mixed $service, mixed $weight): float {
            $weightFloat = is_numeric($weight) ? (float) $weight : 0.0;

            return $weightFloat === 0.5 ? 3.50 : 4.49;
        });

        $dimensions = new DimensionsDto();
        $dimensions->length = 30;
        $dimensions->width = 20;
        $dimensions->height = 15;

        $request = self::createStub(QuoteRequest::class);
        $request->serviceType = 'regular';
        $request->postalCode = '10000';
        $request->weight = 2.4;
        $request->method('getDimensions')->willReturn($dimensions);

        $calculator = new ShippingPriceCalculator($zoneRepo, $serviceTypeRepo, $postOfficeRepo, $tariffRepository, $dataStore);
        $response = $calculator->calculate($request);

        self::assertEquals(5.49, $response->price);
        self::assertEquals('EUR', $response->currency);
        self::assertEquals('regular', $response->serviceType);
        self::assertEquals(2, $response->estimatedDeliveryDays);

        self::assertEquals(3.50, $response->breakdown->base);
        self::assertEquals(0.99, $response->breakdown->weightSurcharge);
        self::assertEquals(0.00, $response->breakdown->dimensionalSurcharge);
        self::assertEquals(1.00, $response->breakdown->zoneSurcharge);
        self::assertEquals(1.0, $response->breakdown->priorityMultiplier);
    }
}
