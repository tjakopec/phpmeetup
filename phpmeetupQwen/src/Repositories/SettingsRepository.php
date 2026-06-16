<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\DomainException;

final class SettingsRepository
{
    public function __construct(
        private readonly string $settingsFilePath
    ) {
    }

    /** Get a specific setting value */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->loadSettings();
        return $settings[$key] ?? $default;
    }

    /** Get all settings as array */
    /** @return array<string, mixed> */
    public function getAll(): array
    {
        return $this->loadSettings();
    }

    /** Get default divisor for volumetric calculation */
    public function getDefaultDivisor(): int
    {
        return (int)$this->get('volumetric_divisor', 5000);
    }

    /** Get VAT percentage */
    public function getVatPercentage(): float
    {
        return (float)$this->get('vat_percentage', 25.0);
    }

    /** Check if rounding is enabled */
    public function isRoundingEnabled(): bool
    {
        return (bool)$this->get('rounding_enabled', true);
    }

    /** Get rounding precision */
    public function getRoundingPrecision(): int
    {
        return (int)$this->get('rounding_precision', 2);
    }

    /** @return array<string, mixed> */
    private function loadSettings(): array
    {
        if (!file_exists($this->settingsFilePath)) {
            throw new DomainException("Settings file not found: {$this->settingsFilePath}");
        }

        $content = file_get_contents($this->settingsFilePath);
        if ($content === false) {
            throw new DomainException("Cannot read settings file: {$this->settingsFilePath}");
        }

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new DomainException("Invalid settings JSON data");
        }

        return $data;
    }
}