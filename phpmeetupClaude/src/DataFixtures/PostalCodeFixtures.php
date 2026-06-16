<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PostalCode;
use App\Entity\ShippingZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostalCodeFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Croatian postal codes: [code, city, zone_reference]
     * Source: Hrvatska Pošta (representative subset)
     */
    private const POSTAL_CODES = [
        // Mainland
        ['10000', 'Zagreb',          ZoneFixtures::MAINLAND_ZONE],
        ['10001', 'Zagreb',          ZoneFixtures::MAINLAND_ZONE],
        ['10010', 'Zagreb - Susedgrad', ZoneFixtures::MAINLAND_ZONE],
        ['10020', 'Zagreb - Novi Zagreb', ZoneFixtures::MAINLAND_ZONE],
        ['10090', 'Zagreb - Susedgrad', ZoneFixtures::MAINLAND_ZONE],
        ['10110', 'Zagreb - Sesvete', ZoneFixtures::MAINLAND_ZONE],
        ['10360', 'Sesvete',         ZoneFixtures::MAINLAND_ZONE],
        ['21000', 'Split',           ZoneFixtures::MAINLAND_ZONE],
        ['21210', 'Solin',           ZoneFixtures::MAINLAND_ZONE],
        ['21220', 'Trogir',          ZoneFixtures::MAINLAND_ZONE],
        ['51000', 'Rijeka',          ZoneFixtures::MAINLAND_ZONE],
        ['51100', 'Rijeka',          ZoneFixtures::MAINLAND_ZONE],
        ['51410', 'Opatija',         ZoneFixtures::MAINLAND_ZONE],
        ['31000', 'Osijek',          ZoneFixtures::MAINLAND_ZONE],
        ['31100', 'Osijek',          ZoneFixtures::MAINLAND_ZONE],
        ['20000', 'Dubrovnik',       ZoneFixtures::MAINLAND_ZONE],
        ['20210', 'Cavtat',          ZoneFixtures::MAINLAND_ZONE],
        ['40000', 'Čakovec',         ZoneFixtures::MAINLAND_ZONE],
        ['42000', 'Varaždin',        ZoneFixtures::MAINLAND_ZONE],
        ['43000', 'Bjelovar',        ZoneFixtures::MAINLAND_ZONE],
        ['44000', 'Sisak',           ZoneFixtures::MAINLAND_ZONE],
        ['47000', 'Karlovac',        ZoneFixtures::MAINLAND_ZONE],
        ['48000', 'Koprivnica',      ZoneFixtures::MAINLAND_ZONE],
        ['49000', 'Krapina',         ZoneFixtures::MAINLAND_ZONE],
        ['52000', 'Pazin',           ZoneFixtures::MAINLAND_ZONE],
        ['52100', 'Pula',            ZoneFixtures::MAINLAND_ZONE],
        ['52210', 'Rovinj',          ZoneFixtures::MAINLAND_ZONE],
        ['52440', 'Poreč',           ZoneFixtures::MAINLAND_ZONE],
        ['53000', 'Gospić',          ZoneFixtures::MAINLAND_ZONE],

        // Islands
        ['21450', 'Hvar',            ZoneFixtures::ISLAND_ZONE],
        ['21460', 'Stari Grad',      ZoneFixtures::ISLAND_ZONE],
        ['21465', 'Jelsa',           ZoneFixtures::ISLAND_ZONE],
        ['51511', 'Malinska',        ZoneFixtures::ISLAND_ZONE],  // Krk
        ['51500', 'Krk',             ZoneFixtures::ISLAND_ZONE],
        ['51550', 'Mali Lošinj',     ZoneFixtures::ISLAND_ZONE],
        ['51557', 'Cres',            ZoneFixtures::ISLAND_ZONE],
        ['23250', 'Pag',             ZoneFixtures::ISLAND_ZONE],
        ['23251', 'Novalja',         ZoneFixtures::ISLAND_ZONE],
        ['20260', 'Korčula',         ZoneFixtures::ISLAND_ZONE],
        ['20271', 'Blato',           ZoneFixtures::ISLAND_ZONE],
        ['20290', 'Lastovo',         ZoneFixtures::ISLAND_ZONE],
        ['21480', 'Vis',             ZoneFixtures::ISLAND_ZONE],
        ['21485', 'Komiža',          ZoneFixtures::ISLAND_ZONE],
        ['23210', 'Biograd na Moru', ZoneFixtures::ISLAND_ZONE],
        ['23281', 'Sali',            ZoneFixtures::ISLAND_ZONE],  // Dugi Otok
        ['21410', 'Postira',         ZoneFixtures::ISLAND_ZONE],  // Brač
        ['21400', 'Supetar',         ZoneFixtures::ISLAND_ZONE],  // Brač

        // Remote / mountain
        ['53220', 'Otočac',          ZoneFixtures::REMOTE_ZONE],
        ['53230', 'Korenica',        ZoneFixtures::REMOTE_ZONE],
        ['53260', 'Brinje',          ZoneFixtures::REMOTE_ZONE],
        ['53270', 'Senj',            ZoneFixtures::REMOTE_ZONE],
        ['53296', 'Vrhovine',        ZoneFixtures::REMOTE_ZONE],
        ['47250', 'Duga Resa',       ZoneFixtures::REMOTE_ZONE],
        ['47300', 'Ogulin',          ZoneFixtures::REMOTE_ZONE],
        ['47240', 'Slunj',           ZoneFixtures::REMOTE_ZONE],
        ['44330', 'Novska',          ZoneFixtures::REMOTE_ZONE],
        ['44400', 'Glina',           ZoneFixtures::REMOTE_ZONE],
        ['44410', 'Petrinja',        ZoneFixtures::REMOTE_ZONE],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::POSTAL_CODES as [$code, $city, $zoneRef]) {
            $postalCode = new PostalCode();
            $postalCode->setCode($code);
            $postalCode->setCity($city);
            $postalCode->setZone($this->getReference($zoneRef, ShippingZone::class));
            $manager->persist($postalCode);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ZoneFixtures::class];
    }
}
