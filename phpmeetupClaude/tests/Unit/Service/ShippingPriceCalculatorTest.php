<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Config\ShippingConfiguration;
use App\Dto\DimensionsInput;
use App\Dto\ShippingQuoteInput;
use App\Entity\PostalCode;
use App\Entity\ShippingZone;
use App\Entity\WeightTariff;
use App\Exception\NoTariffFoundException;
use App\Exception\OversizedParcelException;
use App\Exception\UnresolvablePostalCodeException;
use App\Repository\PostalCodeRepositoryInterface;
use App\Repository\WeightTariffRepositoryInterface;
use App\Service\ShippingPriceCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingPriceCalculatorTest extends TestCase
{
    private PostalCodeRepositoryInterface&MockObject $postalCodeRepo;
    private WeightTariffRepositoryInterface&MockObject $tariffRepo;
    private ShippingConfiguration $config;
    private ShippingPriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->postalCodeRepo = $this->createMock(PostalCodeRepositoryInterface::class);
        $this->tariffRepo = $this->createMock(WeightTariffRepositoryInterface::class);
        $this->config = new ShippingConfiguration(
            volumetricDivisor: 5000,
            priorityMultiplier: 1.5,
            regularDeliveryDaysBase: 3,
            priorityDeliveryDaysBase: 2,
            maxWeight: ['regular' => 30, 'priority' => 30],
            maxDimension: ['regular' => 150, 'priority' => 150],
        );
        $this->calculator = new ShippingPriceCalculator(
            $this->postalCodeRepo,
            $this->tariffRepo,
            $this->config,
        );
    }

    public function testRegularQuoteMainlandWeight1kg(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));
        $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn(
            $this->makeTariff($zone, 0.0, 1.0, 3.00, 0.00),
        );

        $input = new ShippingQuoteInput('10000', 0.5, new DimensionsInput(10, 10, 10), 'regular');
        $output = $this->calculator->calculate($input);

        $this->assertSame('EUR', $output->currency);
        $this->assertSame('regular', $output->serviceType);
        $this->assertSame(3, $output->estimatedDeliveryDays);
        $this->assertSame(0.00, $output->breakdown->weightSurcharge);
        $this->assertSame(0.00, $output->breakdown->zoneSurcharge);
        $this->assertSame(1.0, $output->breakdown->priorityMultiplier);
    }

    public function testRegularQuoteMainlandWeightAtBracketBoundary2kg(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));
        // 2 kg falls in the 2–5 kg bracket
        $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn(
            $this->makeTariff($zone, 2.0, 5.0, 4.50, 0.30),
        );

        $input = new ShippingQuoteInput('10000', 2.0, new DimensionsInput(10, 10, 10), 'regular');
        $output = $this->calculator->calculate($input);

        // base = 4.50 + (2.0 - 2.0) * 0.30 = 4.50
        $this->assertSame(4.50, $output->breakdown->base);
        $this->assertSame(4.50, $output->price);
    }

    public function testVolumetricWeightGreaterThanActualAppliesDimensionalSurcharge(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));
        // volumetric = (50*40*30)/5000 = 12 kg, actual = 2 kg → chargeable = 12 kg
        $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn(
            $this->makeTariff($zone, 10.0, 30.0, 7.40, 0.15),
        );

        $input = new ShippingQuoteInput('10000', 2.0, new DimensionsInput(50, 40, 30), 'regular');
        $output = $this->calculator->calculate($input);

        // volumetric = 12 kg > actual 2 kg → dimensional surcharge = (12 - 2) * 0.15 = 1.50
        $this->assertSame(1.50, $output->breakdown->dimensionalSurcharge);
        $this->assertSame(0.00, $output->breakdown->weightSurcharge);
    }

    public function testIslandZoneAppliesZoneSurcharge(): void
    {
        $zone = $this->makeZone('island', 2.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('21450', $zone));
        $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn(
            $this->makeTariff($zone, 0.0, 1.0, 4.00, 0.00),
        );

        $input = new ShippingQuoteInput('21450', 0.5, new DimensionsInput(10, 10, 10), 'regular');
        $output = $this->calculator->calculate($input);

        $this->assertSame(2.00, $output->breakdown->zoneSurcharge);
        $this->assertSame(6.00, $output->price); // 4.00 base + 2.00 zone
    }

    public function testPriorityQuoteAppliesMultiplierAndReducesDeliveryDays(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));
        $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn(
            $this->makeTariff($zone, 0.0, 1.0, 3.00, 0.00),
        );

        $input = new ShippingQuoteInput('10000', 0.5, new DimensionsInput(10, 10, 10), 'priority');
        $output = $this->calculator->calculate($input);

        $this->assertSame(1.5, $output->breakdown->priorityMultiplier);
        $this->assertSame(2, $output->estimatedDeliveryDays);
        $this->assertSame(4.50, $output->price); // 3.00 * 1.5
    }

    public function testWeightAtExactMaxAllowedSucceeds(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));
        $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn(
            $this->makeTariff($zone, 30.0, null, 10.40, 0.10),
        );

        $input = new ShippingQuoteInput('10000', 30.0, new DimensionsInput(10, 10, 10), 'regular');
        $output = $this->calculator->calculate($input);

        $this->assertSame(30.0, $input->weight);
        $this->assertGreaterThan(0.0, $output->price);
    }

    public function testWeightAboveMaxThrowsOversizedParcelException(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));

        $this->expectException(OversizedParcelException::class);

        $input = new ShippingQuoteInput('10000', 30.001, new DimensionsInput(10, 10, 10), 'regular');
        $this->calculator->calculate($input);
    }

    public function testLongestDimensionAboveMaxThrowsOversizedParcelException(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));

        $this->expectException(OversizedParcelException::class);

        $input = new ShippingQuoteInput('10000', 1.0, new DimensionsInput(151, 10, 10), 'regular');
        $this->calculator->calculate($input);
    }

    public function testUnknownPostalCodeThrowsUnresolvablePostalCodeException(): void
    {
        $this->postalCodeRepo->method('findByCode')->willReturn(null);

        $this->expectException(UnresolvablePostalCodeException::class);

        $input = new ShippingQuoteInput('99999', 1.0, new DimensionsInput(10, 10, 10), 'regular');
        $this->calculator->calculate($input);
    }

    public function testVolumetricEqualsActualNoDimensionalSurcharge(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));
        // volumetric = (10*10*10)/5000 = 0.2 kg, actual = 0.2 kg → equal, no surcharge
        $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn(
            $this->makeTariff($zone, 0.0, 1.0, 3.00, 0.00),
        );

        $input = new ShippingQuoteInput('10000', 0.2, new DimensionsInput(10, 10, 10), 'regular');
        $output = $this->calculator->calculate($input);

        $this->assertSame(0.00, $output->breakdown->dimensionalSurcharge);
    }

    public function testNoTariffFoundThrowsNoTariffFoundException(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));
        $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn(null);

        $this->expectException(NoTariffFoundException::class);

        $input = new ShippingQuoteInput('10000', 1.0, new DimensionsInput(10, 10, 10), 'regular');
        $this->calculator->calculate($input);
    }

    public function testAllWeightBracketTiers(): void
    {
        $zone = $this->makeZone('mainland', 0.00);
        $this->postalCodeRepo->method('findByCode')->willReturn($this->makePostalCode('10000', $zone));

        $brackets = [
            [0.5,  $this->makeTariff($zone, 0.0,  1.0,  3.00, 0.00), 3.00],
            [1.5,  $this->makeTariff($zone, 1.0,  2.0,  3.50, 0.50), 3.75],  // 3.50 + 0.5*0.50
            [3.0,  $this->makeTariff($zone, 2.0,  5.0,  4.50, 0.30), 4.80],  // 4.50 + 1.0*0.30
            [7.0,  $this->makeTariff($zone, 5.0,  10.0, 5.40, 0.20), 5.80],  // 5.40 + 2.0*0.20
            [15.0, $this->makeTariff($zone, 10.0, 30.0, 7.40, 0.15), 8.15],  // 7.40 + 5.0*0.15
            [35.0, $this->makeTariff($zone, 30.0, null, 10.40, 0.10), 10.90], // 10.40 + 5.0*0.10
        ];

        foreach ($brackets as [$weight, $tariff, $expectedBase]) {
            $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn($tariff);
            $input = new ShippingQuoteInput('10000', $weight, new DimensionsInput(10, 10, 10), 'regular');

            // Reset mock for each iteration
            $this->tariffRepo = $this->createMock(WeightTariffRepositoryInterface::class);
            $this->tariffRepo->method('findBracketForZoneAndWeight')->willReturn($tariff);
            $this->calculator = new ShippingPriceCalculator($this->postalCodeRepo, $this->tariffRepo, $this->config);

            $output = $this->calculator->calculate($input);
            $this->assertEqualsWithDelta($expectedBase, $output->breakdown->base, 0.01, "Failed for weight {$weight}");
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function makeZone(string $name, float $surcharge): ShippingZone
    {
        $zone = new ShippingZone();
        $zone->setName($name);
        $zone->setZoneSurcharge($surcharge);

        // Use reflection to set the ID so it's not null
        $ref = new \ReflectionProperty(ShippingZone::class, 'id');
        $ref->setValue($zone, 1);

        return $zone;
    }

    private function makePostalCode(string $code, ShippingZone $zone): PostalCode
    {
        $pc = new PostalCode();
        $pc->setCode($code);
        $pc->setCity('Test City');
        $pc->setZone($zone);

        return $pc;
    }

    private function makeTariff(
        ShippingZone $zone,
        float $minWeight,
        ?float $maxWeight,
        float $basePrice,
        float $unitPrice,
    ): WeightTariff {
        $tariff = new WeightTariff();
        $tariff->setZone($zone);
        $tariff->setMinWeight($minWeight);
        $tariff->setMaxWeight($maxWeight);
        $tariff->setBasePrice($basePrice);
        $tariff->setWeightUnitPrice($unitPrice);

        return $tariff;
    }
}
