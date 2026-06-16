<?php

declare(strict_types=1);

namespace App\Parsers;

use App\Exceptions\DomainException;

final class ZoneParser
{
    /** @return array<string, mixed> */
    public static function parseFile(string $path): array
    {
        if (!file_exists($path)) {
            throw new DomainException("Zone file not found: {$path}");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new DomainException("Cannot read zone file: {$path}");
        }

        // Parse YAML manually (simple structure)
        return self::parseYaml($content);
    }

    /** @return array<string, mixed> */
    public static function parseYaml(string $yaml): array
    {
        $result = [];
        $lines = explode("\n", $yaml);
        $currentZone = null;
        $currentSection = null;

        foreach ($lines as $line) {
            // Skip comments and empty lines
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '---')) {
                continue;
            }

            $indent = strlen($line) - strlen(ltrim($line));

            // Top-level key (zones)
            if ($indent === 0 && str_ends_with($trimmed, ':')) {
                $currentZone = rtrim($trimmed, ':');
                $result[$currentZone] = [];
                continue;
            }

            // Zone code
            if ($indent === 4 && str_ends_with($trimmed, ':')) {
                $currentSection = rtrim($trimmed, ':');
                $result[$currentZone][$currentSection] = [];
                continue;
            }

            // Values
            if (str_contains($trimmed, ':') && $currentZone !== null) {
                [$key, $value] = explode(':', $trimmed, 2);
                $value = trim($value);

                // Convert numeric values
                if (is_numeric($value)) {
                    $value = strpos($value, '.') !== false ? (float)$value : (int)$value;
                }

                if ($currentSection !== null) {
                    $result[$currentZone][$currentSection][trim($key)] = $value;
                } else {
                    $result[$currentZone][$key] = $value;
                }
            }
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public static function parseJson(string $json): array
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new DomainException("Invalid zone JSON data");
        }
        return $data;
    }
}