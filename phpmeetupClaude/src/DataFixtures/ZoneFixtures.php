<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ShippingZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ZoneFixtures extends Fixture
{
    public const MAINLAND_ZONE = 'zone_mainland';
    public const ISLAND_ZONE = 'zone_island';
    public const REMOTE_ZONE = 'zone_remote';

    public function load(ObjectManager $manager): void
    {
        $mainland = new ShippingZone();
        $mainland->setName('mainland');
        $mainland->setZoneSurcharge(0.00);
        $mainland->setDescription('Croatian mainland — standard delivery area');
        $manager->persist($mainland);
        $this->addReference(self::MAINLAND_ZONE, $mainland);

        $island = new ShippingZone();
        $island->setName('island');
        $island->setZoneSurcharge(2.00);
        $island->setDescription('Croatian islands (Hvar, Krk, Pag, Korčula, Lastovo, Vis, etc.)');
        $manager->persist($island);
        $this->addReference(self::ISLAND_ZONE, $island);

        $remote = new ShippingZone();
        $remote->setName('remote');
        $remote->setZoneSurcharge(1.50);
        $remote->setDescription('Remote and mountain areas of Croatia');
        $manager->persist($remote);
        $this->addReference(self::REMOTE_ZONE, $remote);

        $manager->flush();
    }
}
