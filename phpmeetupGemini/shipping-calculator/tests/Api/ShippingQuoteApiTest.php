<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ShippingQuoteApiTest extends ApiTestCase
{
    public function testCreateQuote()
    {
        $client = static::createClient();

        $response = $client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'dimensions' => [
                    'length' => 10,
                    'width' => 10,
                    'height' => 10
                ],
                'weight' => 1.5,
                'serviceType' => 'regular'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'totalPrice' => 5,
            'estimatedDeliveryDays' => 3,
            'breakdown' => [
                'basePrice' => 5,
                'zoneSurcharge' => 0,
                'prioritySurcharge' => 0
            ],
            'chargeableWeight' => 1.5
        ]);
    }

    public function testValidationFailsOnInvalidPostalCode()
    {
        $client = static::createClient();

        $response = $client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '99999',
                'dimensions' => [
                    'length' => 10,
                    'width' => 10,
                    'height' => 10
                ],
                'weight' => 1.5,
                'serviceType' => 'regular'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
