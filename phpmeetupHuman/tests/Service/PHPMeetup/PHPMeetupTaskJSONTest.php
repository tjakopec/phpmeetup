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

class PHPMeetupTaskJSONTest extends TestCase
{
    public function testCalculateWithExactPhpMeetupData(): void
    {
        $inputJson = '{
          "postalCode": "10000",
          "weight": 2.4,
          "dimensions": { "length": 30, "width": 20, "height": 15 },
          "serviceType": "regular"
        }';

        $inputData = json_decode($inputJson, true, 512, JSON_THROW_ON_ERROR);

        $dimensions = new DimensionsDto();
        $dimensions->length = $inputData['dimensions']['length'];
        $dimensions->width = $inputData['dimensions']['width'];
        $dimensions->height = $inputData['dimensions']['height'];

        $request = self::createStub(QuoteRequest::class);
        $request->serviceType = $inputData['serviceType'];
        $request->postalCode = $inputData['postalCode'];
        $request->weight = $inputData['weight'];
        $request->method('getDimensions')->willReturn($dimensions);

        $zoneRepo = self::createStub(ShippingZoneRepository::class);
        $serviceTypeRepo = self::createStub(ServiceTypeRepository::class);
        $postOfficeRepo = self::createStub(PostOfficeRepository::class);
        $tariffRepository = self::createStub(TariffRepository::class);
        $dataStore = self::createStub(RequestDataStore::class);

        $dataStore->method('get')->willReturn(null);

        $serviceTypeEntity = self::createStub(ServiceType::class);
        $serviceTypeEntity->method('getCode')->willReturn('regular');
        $serviceTypeEntity->method('getVolumeDivisor')->willReturn(5000);
        $serviceTypeEntity->method('getDimensionalSurcharge')->willReturn(0.00);
        $serviceTypeEntity->method('getPriorityMultiplier')->willReturn(1.0);
        $serviceTypeEntity->method('getReducesEstimatedDeliveryDays')->willReturn(0);
        $serviceTypeRepo->method('findOneBy')->willReturn($serviceTypeEntity);

        $zone = self::createStub(ShippingZone::class);
        $zone->method('getZoneSurcharge')->willReturn(1.00);
        $zone->method('getBaseDeliveryDays')->willReturn(2);
        $zoneRepo->method('findOneByPostalCode')->willReturn($zone);

        $postOffice = self::createStub(PostOffice::class);
        $postOffice->method('getCurrency')->willReturn('EUR');
        $postOfficeRepo->method('findOneByPostalCode')->willReturn($postOffice);

        $tariffRepository->method('findPriceByServiceAndWeight')
            ->willReturnCallback(function (mixed $service, mixed $w): float {
                $weightFloat = is_numeric($w) ? (float) $w : 0.0;

                return $weightFloat === 0.5 ? 3.50 : 4.49;
            });

        $calculator = new ShippingPriceCalculator($zoneRepo, $serviceTypeRepo, $postOfficeRepo, $tariffRepository, $dataStore);
        $response = $calculator->calculate($request);

        $expectedJson = '{
          "price": 5.49,
          "currency": "EUR",
          "serviceType": "regular",
          "estimatedDeliveryDays": 2,
          "breakdown": {
            "base": 3.50,
            "weightSurcharge": 0.99,
            "dimensionalSurcharge": 0.00,
            "zoneSurcharge": 1.00,
            "priorityMultiplier": 1.0
          }
        }';

        // Asertacija
        self::assertJsonStringEqualsJsonString(
            $expectedJson,
            json_encode($response, JSON_THROW_ON_ERROR)
        );
    }
}
