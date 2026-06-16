<?php

declare(strict_types=1);

namespace App\Parsers;

use App\Exceptions\DomainException;

final class TariffParser
{
    /** @return array<string, mixed> */
    public static function parseFile(string $path): array
    {
        if (!file_exists($path)) {
            throw new DomainException("Tariff file not found: {$path}");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new DomainException("Cannot read tariff file: {$path}");
        }

        return self::parseJson($content);
    }

    /** @return array<string, mixed> */
    public static function parseJson(string $json): array
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new DomainException("Invalid tariff JSON data");
        }

        // Validate required fields
        if (!isset($data['service_type']) || !isset($data['currency']) || !isset($data['weight_tiers'])) {
            throw new DomainException("Tariff data must contain 'service_type', 'currency', and 'weight_tiers'");
        }

        return $data;
    }
}