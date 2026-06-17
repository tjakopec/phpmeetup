<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\DomainException;
use App\Parsers\ZoneParser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ZoneRepository
{
    /** @var array<string, mixed>|null */
    private ?array $zones = null;

    public function __construct(
        #[Autowire('%kernel.project_dir%/config/shipping/zones.yaml')]
        private readonly string $zoneFilePath
    ) {
    }

    /** Get zone data by code */
    public function getZone(string $code): array
    {
        $zones = $this->loadZones();

        if (!isset($zones['zones'][$code])) {
            throw new DomainException("Zone not found: {$code}");
        }

        return $zones['zones'][$code];
    }

    /** Get zone surcharge for a destination zone */
    public function getZoneSurcharge(string $destinationCode): float
    {
        $zone = $this->getZone($destinationCode);

        if (!isset($zone['surcharge'])) {
            throw new DomainException("Zone surcharge not defined for: {$destinationCode}");
        }

        return (float)$zone['surcharge'];
    }

    /** Check if zone code is valid */
    public function isValidZone(string $code): bool
    {
        $zones = $this->loadZones();
        return isset($zones['zones'][$code]);
    }

    /** @return array<string, mixed> */
    private function loadZones(): array
    {
        if ($this->zones === null) {
            $this->zones = ZoneParser::parseFile($this->zoneFilePath);
        }
        return $this->zones;
    }

    /** Get all available zone codes */
    /** @return string[] */
    public function getAllZoneCodes(): array
    {
        $zones = $this->loadZones();
        return array_keys($zones['zones'] ?? []);
    }
}