<?php

namespace App\Tests\Service\ShippingCalculator;

use App\Repository\PostOfficeRepository;
use App\Repository\ServiceTypeRepository;
use App\Repository\ShippingZoneRepository;
use App\Repository\TariffRepository;
use App\Service\RequestDataStore;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class TestBase extends TestCase
{
    /** @var ShippingZoneRepository&MockObject
     */
    protected ShippingZoneRepository $zoneRepo;

    /** @var ServiceTypeRepository&MockObject
     */
    protected ServiceTypeRepository $serviceTypeRepo;

    /** @var PostOfficeRepository&MockObject
     */
    protected PostOfficeRepository $postOfficeRepo;

    /** @var TariffRepository&MockObject
     */
    protected TariffRepository $tariffRepository;

    /** @var RequestDataStore&MockObject
     */
    protected RequestDataStore $dataStore;

    protected function setUp(): void
    {
        $this->zoneRepo = $this->createMock(ShippingZoneRepository::class);
        $this->serviceTypeRepo = $this->createMock(ServiceTypeRepository::class);
        $this->postOfficeRepo = $this->createMock(PostOfficeRepository::class);
        $this->tariffRepository = $this->createMock(TariffRepository::class);
        $this->dataStore = $this->createMock(RequestDataStore::class);

        $this->dataStore->method('get')->willReturn(null);
    }
}
