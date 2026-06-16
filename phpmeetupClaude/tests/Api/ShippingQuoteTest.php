<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;

class ShippingQuoteTest extends ApiTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    // ─── Happy path tests ────────────────────────────────────────────────────────

    public function testHappyPathRegularMainlandZip(): void
    {
        $response = $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'weight' => 2.4,
                'dimensions' => ['length' => 30, 'width' => 20, 'height' => 15],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('currency', $data);
        $this->assertArrayHasKey('serviceType', $data);
        $this->assertArrayHasKey('estimatedDeliveryDays', $data);
        $this->assertArrayHasKey('breakdown', $data);
        $this->assertSame('EUR', $data['currency']);
        $this->assertSame('regular', $data['serviceType']);
        $this->assertSame(3, $data['estimatedDeliveryDays']);
        $this->assertArrayHasKey('base', $data['breakdown']);
        $this->assertArrayHasKey('weightSurcharge', $data['breakdown']);
        $this->assertArrayHasKey('dimensionalSurcharge', $data['breakdown']);
        $this->assertArrayHasKey('zoneSurcharge', $data['breakdown']);
        $this->assertArrayHasKey('priorityMultiplier', $data['breakdown']);
        $this->assertSame(1.0, $data['breakdown']['priorityMultiplier']);
        $this->assertSame(0.0, $data['breakdown']['zoneSurcharge']);
    }

    public function testHappyPathPriorityMainlandZip(): void
    {
        $response = $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'weight' => 1.0,
                'dimensions' => ['length' => 20, 'width' => 15, 'height' => 10],
                'serviceType' => 'priority',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertSame('priority', $data['serviceType']);
        $this->assertSame(2, $data['estimatedDeliveryDays']);
        $this->assertSame(1.5, $data['breakdown']['priorityMultiplier']);
    }

    public function testHappyPathIslandZip(): void
    {
        $response = $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '21450',  // Hvar — island zone
                'weight' => 1.0,
                'dimensions' => ['length' => 20, 'width' => 15, 'height' => 10],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertSame(2.0, $data['breakdown']['zoneSurcharge']);
    }

    public function testHappyPathRemoteZip(): void
    {
        $response = $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '53220',  // Otočac — remote zone
                'weight' => 1.0,
                'dimensions' => ['length' => 20, 'width' => 15, 'height' => 10],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertSame(1.5, $data['breakdown']['zoneSurcharge']);
    }

    // ─── Validation error tests ──────────────────────────────────────────────────

    public function testInvalidZipUnknownReturns422(): void
    {
        $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '99999',
                'weight' => 1.0,
                'dimensions' => ['length' => 20, 'width' => 15, 'height' => 10],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testNegativeWeightReturns422(): void
    {
        $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'weight' => -1.0,
                'dimensions' => ['length' => 20, 'width' => 15, 'height' => 10],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testZeroDimensionReturns422(): void
    {
        $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'weight' => 1.0,
                'dimensions' => ['length' => 0, 'width' => 15, 'height' => 10],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testMissingPostalCodeFieldReturns422(): void
    {
        $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'weight' => 1.0,
                'dimensions' => ['length' => 20, 'width' => 15, 'height' => 10],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testOversizedParcelWeightReturns422(): void
    {
        $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'weight' => 31.0,
                'dimensions' => ['length' => 20, 'width' => 15, 'height' => 10],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testOversizedDimensionReturns422(): void
    {
        $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'weight' => 1.0,
                'dimensions' => ['length' => 160, 'width' => 15, 'height' => 10],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testInvalidServiceTypeReturns422(): void
    {
        $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'weight' => 1.0,
                'dimensions' => ['length' => 20, 'width' => 15, 'height' => 10],
                'serviceType' => 'express',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testMalformedJsonReturns400(): void
    {
        $this->client->request('POST', '/api/shipping/quotes', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => '{invalid json',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testResponseStructureMatchesExpectedSchema(): void
    {
        $response = $this->client->request('POST', '/api/shipping/quotes', [
            'json' => [
                'postalCode' => '10000',
                'weight' => 2.4,
                'dimensions' => ['length' => 30, 'width' => 20, 'height' => 15],
                'serviceType' => 'regular',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        // Verify all required fields are present and have correct types
        $this->assertIsFloat($data['price']);
        $this->assertIsString($data['currency']);
        $this->assertIsString($data['serviceType']);
        $this->assertIsInt($data['estimatedDeliveryDays']);
        $this->assertIsArray($data['breakdown']);
        $this->assertIsFloat($data['breakdown']['base']);
        $this->assertIsFloat($data['breakdown']['weightSurcharge']);
        $this->assertIsFloat($data['breakdown']['dimensionalSurcharge']);
        $this->assertIsFloat($data['breakdown']['zoneSurcharge']);
        $this->assertIsFloat($data['breakdown']['priorityMultiplier']);
        $this->assertGreaterThan(0.0, $data['price']);
    }
}
