<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ShippingZone;
use App\Entity\WeightTariff;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WeightTariffFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Weight brackets: [minWeight, maxWeight|null, basePrice, weightUnitPrice]
     * maxWeight null = no upper limit (open-ended bracket)
     */
    private const MAINLAND_BRACKETS = [
        [0.0,   1.0,   3.00, 0.00],
        [1.0,   2.0,   3.50, 0.50],
        [2.0,   5.0,   4.50, 0.30],
        [5.0,   10.0,  5.40, 0.20],
        [10.0,  30.0,  7.40, 0.15],
        [30.0,  null, 10.40, 0.10],
    ];

    /** Island brackets: same structure, base_price +1.00 per bracket */
    private const ISLAND_BRACKETS = [
        [0.0,   1.0,   4.00, 0.00],
        [1.0,   2.0,   4.50, 0.50],
        [2.0,   5.0,   5.50, 0.30],
        [5.0,   10.0,  6.40, 0.20],
        [10.0,  30.0,  8.40, 0.15],
        [30.0,  null, 11.40, 0.10],
    ];

    /** Remote brackets: same structure, base_price +1.00 per bracket */
    private const REMOTE_BRACKETS = [
        [0.0,   1.0,   4.00, 0.00],
        [1.0,   2.0,   4.50, 0.50],
        [2.0,   5.0,   5.50, 0.30],
        [5.0,   10.0,  6.40, 0.20],
        [10.0,  30.0,  8.40, 0.15],
        [30.0,  null, 11.40, 0.10],
    ];

    public function load(ObjectManager $manager): void
    {
        $this->loadBracketsForZone(
            $manager,
            $this->getReference(ZoneFixtures::MAINLAND_ZONE, ShippingZone::class),
            self::MAINLAND_BRACKETS,
        );

        $this->loadBracketsForZone(
            $manager,
            $this->getReference(ZoneFixtures::ISLAND_ZONE, ShippingZone::class),
            self::ISLAND_BRACKETS,
        );

        $this->loadBracketsForZone(
            $manager,
            $this->getReference(ZoneFixtures::REMOTE_ZONE, ShippingZone::class),
            self::REMOTE_BRACKETS,
        );

        $manager->flush();
    }

    /**
     * @param array<int, array{0: float, 1: float|null, 2: float, 3: float}> $brackets
     */
    private function loadBracketsForZone(ObjectManager $manager, ShippingZone $zone, array $brackets): void
    {
        foreach ($brackets as [$minWeight, $maxWeight, $basePrice, $unitPrice]) {
            $tariff = new WeightTariff();
            $tariff->setZone($zone);
            $tariff->setMinWeight($minWeight);
            $tariff->setMaxWeight($maxWeight);
            $tariff->setBasePrice($basePrice);
            $tariff->setWeightUnitPrice($unitPrice);
            $manager->persist($tariff);
        }
    }

    public function getDependencies(): array
    {
        return [ZoneFixtures::class];
    }
}
