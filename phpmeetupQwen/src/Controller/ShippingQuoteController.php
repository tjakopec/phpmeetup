<?php

declare(strict_types=1);

namespace App\Controller;

use App\Calculator\ShippingCalculator;
use App\Dto\ParcelDimensions;
use App\Dto\ShippingQuoteRequest;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Shipping Quote Controller
 * Handles API requests for shipping quotes
 */
final class ShippingQuoteController
{
    public function __construct(
        private readonly ShippingCalculator $calculator
    ) {
    }

    /**
     * POST /api/shipping/quote
     * 
     * Calculate shipping quote based on package details and destination
     */
    #[Route('/api/shipping/quote', name: 'app_shipping_quote', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            
            // Parse and validate request
            $shippingRequest = $this->parseRequest($payload);

            // Calculate the shipping quote
            $quote = $this->calculator->calculate($shippingRequest);

            return new JsonResponse([
                'success' => true,
                'data' => $quote->toArray(),
            ], Response::HTTP_OK);

        } catch (ValidationException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (BadRequestHttpException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'BAD_REQUEST',
                    'message' => 'Invalid request payload format.',
                    'details' => $e->getMessage(),
                ],
            ], Response::HTTP_BAD_REQUEST);

        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_INPUT',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred.',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Parse the JSON payload into a ShippingQuoteRequest DTO
     */
    private function parseRequest(array $payload): ShippingQuoteRequest
    {
        // Validate required fields exist
        $requiredFields = ['weight_kg', 'destination_zone'];
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                throw new BadRequestHttpException("Missing required field: {$field}");
            }
        }

        // Parse dimensions if provided
        $dimensions = null;
        if (isset($payload['dimensions']) && is_array($payload['dimensions'])) {
            $dims = $payload['dimensions'];
            
            if (!isset($dims['width_cm'], $dims['height_cm'], $dims['length_cm'])) {
                throw new BadRequestHttpException('Dimensions must include width_cm, height_cm, and length_cm');
            }

            $dimensions = new ParcelDimensions(
                (float)$dims['width_cm'],
                (float)$dims['height_cm'],
                (float)$dims['length_cm']
            );
        }

        // Parse service type if provided
        $serviceType = null;
        if (isset($payload['service_type'])) {
            $serviceType = \App\Enums\ServiceType::from(strtolower($payload['service_type']));
        }

        return new ShippingQuoteRequest(
            weightKg: (float)$payload['weight_kg'],
            destinationZone: (string)$payload['destination_zone'],
            dimensions: $dimensions,
            serviceType: $serviceType,
            hasInsurance: (bool)($payload['has_insurance'] ?? false),
            insuranceValue: isset($payload['insurance_value']) ? (float)$payload['insurance_value'] : null,
            isFragile: (bool)($payload['is_fragile'] ?? false)
        );
    }
}