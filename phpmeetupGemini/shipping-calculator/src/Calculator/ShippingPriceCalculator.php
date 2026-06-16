<?php

namespace App\Calculator;

use App\Dto\PriceBreakdown;
use App\Dto\ShippingQuoteRequest;
use App\Dto\ShippingQuoteResponse;

class ShippingPriceCalculator implements ShippingPriceCalculatorInterface
{
    private PricingConfig $config;

    public function __construct(PricingConfig $config)
    {
        $this->config = $config;
    }

    public function calculate(ShippingQuoteRequest $request): ShippingQuoteResponse
    {
        $volumetricWeight = ($request->dimensions->length * $request->dimensions->width * $request->dimensions->height) / $this->config->volumetricDivisor;
        $chargeableWeight = max($request->weight, $volumetricWeight);

        $zone = $this->config->resolveZone($request->postalCode);
        
        $basePrice = $this->config->getBasePrice($chargeableWeight);
        $zoneSurcharge = $this->config->zoneSurcharges[$zone] ?? 0.0;
        
        $subtotal = $basePrice + $zoneSurcharge;
        
        $prioritySurcharge = 0.0;
        $estimatedDays = 3;
        
        if ($request->serviceType === 'priority') {
            $totalPrice = $subtotal * $this->config->priorityMultiplier;
            $prioritySurcharge = $totalPrice - $subtotal;
            $estimatedDays = 1;
        } else {
            $totalPrice = $subtotal;
        }

        $breakdown = new PriceBreakdown();
        $breakdown->basePrice = round($basePrice, 2);
        $breakdown->zoneSurcharge = round($zoneSurcharge, 2);
        $breakdown->prioritySurcharge = round($prioritySurcharge, 2);

        $response = new ShippingQuoteResponse();
        $response->totalPrice = round($totalPrice, 2);
        $response->estimatedDeliveryDays = $estimatedDays;
        $response->breakdown = $breakdown;
        $response->chargeableWeight = round($chargeableWeight, 2);

        return $response;
    }
}
