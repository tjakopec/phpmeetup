<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class ApiRateLimiterTest extends WebTestCase
{
    public function testShippingQuotesRateLimiter(): void
    {
        $client = static::createClient();

        $payloadData = json_encode([
            'postalCode' => '10000',
            'weight' => 2.4,
            'dimensions' => [
                'length' => 30,
                'width' => 20,
                'height' => 15,
            ],
            'serviceType' => 'regular',
        ], JSON_THROW_ON_ERROR);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        // 1. Zahtjev (Troši 1. token -> Ostaje 1)
        $client->request('POST', '/api/shipping/quotes', [], [], $headers, $payloadData);
        self::assertResponseIsSuccessful();

        // 2. Zahtjev (Troši 2. token -> Ostaje 0)
        $client->request('POST', '/api/shipping/quotes', [], [], $headers, $payloadData);
        self::assertResponseIsSuccessful();

        // 3. Zahtjev (Nema više tokena -> Očekujemo 429)
        $client->request('POST', '/api/shipping/quotes', [], [], $headers, $payloadData);
        self::assertResponseStatusCodeSame(429);
    }

    public function testRateLimiterHasCorrectLimitInConfiguration(): void
    {
        self::bootKernel();

        // Dohvaćamo factory direktno preko ID-ja servisa
        /** @var RateLimiterFactory $rateLimiterFactory */
        $rateLimiterFactory = self::getContainer()->get('limiter.api_limiter');

        // Kreiramo testni limiter
        $limiter = $rateLimiterFactory->create('test_config_key');

        // Dohvaćamo trenutno stanje (limiter state)
        $limiterState = $limiter->consume(0);

        // Provjeravamo da li je limit postavljen na očekivanu vrijednost
        self::assertSame(2, $limiterState->getLimit(), 'Limit u testnom okruženju bi trebao biti 2');
    }
}
