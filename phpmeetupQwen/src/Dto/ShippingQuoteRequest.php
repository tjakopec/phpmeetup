<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\ServiceType;
use App\Exceptions\ValidationException;

final class ShippingQuoteRequest
{
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        // Validate required fields
        $missing = [];
        foreach (['service_type', 'origin_zone_code', 'destination_zone_code'] as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new ValidationException(
                array_combine($missing, array_map(fn($f) => "Missing required field: {$f}", $missing)),
                422
            );
        }

        // Parse service type
        $serviceType = ServiceType::fromLabel((string)$data['service_type']);

        // Build parcel if not provided
        $parcel = $data['parcel'] ?? [
            'weight_kg' => 1.0,
            'length_cm' => 30,
            'width_cm' => 20,
            'height_cm' => 15,
        ];

        // Validate parcel dimensions
        $parcelErrors = self::validateParcel($parcel);
        if (!empty($parcelErrors)) {
            throw new ValidationException($parcelErrors, 422);
        }

        return new self(
            $serviceType,
            (string)$data['origin_zone_code'],
            (string)$data['destination_zone_code'],
            $parcel
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'service_type' => $this->serviceType->value,
            'origin_zone_code' => $this->originZoneCode,
            'destination_zone_code' => $this->destinationZoneCode,
            'parcel' => $this->parcel->toArray(),
        ];
    }

    public function __construct(
        private ServiceType $serviceType,
        private string $originZoneCode,
        private string $destinationZoneCode,
        private ParcelDimensions $parcel
    ) {
    }

    public function getServiceType(): ServiceType
    {
        return $this->serviceType;
    }

    public function getOriginZoneCode(): string
    {
        return $this->originZoneCode;
    }

    public function getDestinationZoneCode(): string
    {
        return $this->destinationZoneCode;
    }

    public function getParcel(): ParcelDimensions
    {
        return $this->parcel;
    }

    /** @return array<string, string[]> */
    private static function validateParcel(array $parcel): array
    {
        $errors = [];

        if (!isset($parcel['weight_kg']) || $parcel['weight_kg'] <= 0) {
            $errors['weight_kg'] = ['Weight must be greater than 0'];
        }

        foreach (['length_cm', 'width_cm', 'height_cm'] as $dim) {
            if (!isset($parcel[$dim]) || $parcel[$dim] <= 0) {
                $errors[$dim][] = "{$dim} must be greater than 0";
            }
        }

        return $errors;
    }
}