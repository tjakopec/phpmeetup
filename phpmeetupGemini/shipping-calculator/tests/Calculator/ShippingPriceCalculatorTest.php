<?php

namespace App\Tests\Calculator;

use App\Calculator\PricingConfig;
use App\Calculator\ShippingPriceCalculator;
use App\Dto\Dimensions;
use App\Dto\ShippingQuoteRequest;
use PHPUnit\Framework\TestCase;

class ShippingPriceCalculatorTest extends TestCase
{
    private ShippingPriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new ShippingPriceCalculator(new PricingConfig());
    }

    public function testRegularMainland()
    {
        $request = new ShippingQuoteRequest();
        $request->postalCode = '10000'; // mainland
        $request->weight = 1.0;
        $request->serviceType = 'regular';
        
        $dims = new Dimensions();
        $dims->length = 10;
        $dims->width = 10;
        $dims->height = 10;
        $request->dimensions = $dims;

        $response = $this->calculator->calculate($request);

        // Volumetric weight = (10*10*10) / 5000 = 0.2
        // Chargeable weight = max(1.0, 0.2) = 1.0
        // Tier 1 (0-2kg) = 5.0
        // Zone mainland = 0.0 surcharge
        $this->assertEquals(5.0, $response->totalPrice);
        $this->assertEquals(5.0, $response->breakdown->basePrice);
        $this->assertEquals(0.0, $response->breakdown->zoneSurcharge);
        $this->assertEquals(0.0, $response->breakdown->prioritySurcharge);
        $this->assertEquals(3, $response->estimatedDeliveryDays);
    }

    public function testPriorityIslandsVolumetricWeight()
    {
        $request = new ShippingQuoteRequest();
        $request->postalCode = '21000'; // islands
        $request->weight = 1.0;
        $request->serviceType = 'priority';
        
        $dims = new Dimensions();
        $dims->length = 50;
        $dims->width = 50;
        $dims->height = 50;
        $request->dimensions = $dims;

        $response = $this->calculator->calculate($request);

        // Volumetric weight = (50*50*50) / 5000 = 25.0
        // Chargeable weight = max(1.0, 25.0) = 25.0
        // Tier max fallback = 25.0
        // Zone islands = 5.0 surcharge
        // Subtotal = 25.0 + 5.0 = 30.0
        // Priority multiplier 1.5 -> Total = 45.0
        // Priority surcharge = 15.0

        $this->assertEquals(45.0, $response->totalPrice);
        $this->assertEquals(25.0, $response->breakdown->basePrice);
        $this->assertEquals(5.0, $response->breakdown->zoneSurcharge);
        $this->assertEquals(15.0, $response->breakdown->prioritySurcharge);
        $this->assertEquals(1, $response->estimatedDeliveryDays);
    }
}
