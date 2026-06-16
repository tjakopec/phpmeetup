<?php

declare(strict_types=1);

namespace App;

use App\Calculator\ShippingCalculator;
use App\Repositories\SettingsRepository;
use App\Repositories\TariffRepository;
use App\Repositories\ZoneRepository;
use App\Controller\ShippingQuoteController;

/**
 * Application Kernel - Boots the application and sets up dependency injection
 */
final class Kernel
{
    private readonly ShippingCalculator $calculator;
    private readonly ShippingQuoteController $controller;

    public function __construct(
        private readonly string $projectDir
    ) {
    }

    /** Get the configured shipping calculator */
    public function getShippingCalculator(): ShippingCalculator
    {
        return $this->calculator;
    }

    /** Get the shipping quote controller */
    public function getShippingQuoteController(): ShippingQuoteController
    {
        return $this->controller;
    }

    /** Boot the application and initialize services */
    public function boot(): void
    {
        $tariffsDir = "{$this->projectDir}/config/shipping";
        
        $tariffRepo = new TariffRepository($tariffsDir);
        $zoneRepo = new ZoneRepository("{$this->projectDir}/config/shipping/zones.yaml");
        $settingsRepo = new SettingsRepository("{$this->projectDir}/config/shipping/settings.json");

        $this->calculator = new ShippingCalculator(
            $tariffRepo,
            $zoneRepo,
            $settingsRepo
        );

        $this->controller = new ShippingQuoteController($this->calculator);
    }
}