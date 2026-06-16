# Shipping Price Calculator

This project implements a stateless shipping price calculation REST API endpoint (`POST /api/shipping/quotes`) using API Platform and Symfony.

## Setup

1. `composer install`
2. `symfony server:start` or use any other web server configuration

## Configuration

Pricing configuration (weight tiers, zone mapping, multipliers) is managed in `src/Calculator/PricingConfig.php`.
You can easily adjust the arrays and properties within this class to modify rates.

## Usage

Send a POST request to `/api/shipping/quotes` with a JSON payload:

```json
{
  "postalCode": "10000",
  "dimensions": {
    "length": 10,
    "width": 10,
    "height": 10
  },
  "weight": 1.5,
  "serviceType": "regular"
}
```

Response:

```json
{
  "totalPrice": 5.0,
  "estimatedDeliveryDays": 3,
  "breakdown": {
    "basePrice": 5.0,
    "zoneSurcharge": 0.0,
    "prioritySurcharge": 0.0
  },
  "chargeableWeight": 1.5
}
```

## Running Tests

Run unit and functional API tests via PHPUnit:

```bash
./vendor/bin/phpunit
```
