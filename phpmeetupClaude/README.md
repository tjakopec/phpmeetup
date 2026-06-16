# Shipping Price Calculator API

A stateless REST API built with **Symfony 7** and **API Platform 3** for calculating shipping prices for parcels within Croatia.

## Quick Start

```bash
# Install dependencies (requires internet access to packagist.org)
make install

# Set up database, run migrations, and seed fixtures
make setup

# Start the development server
php -S localhost:8000 -t public/
```

## Shipping Quote API

### Endpoint Contract

```
POST /api/shipping/quotes
Content-Type: application/json
```

**Request body:**

```json
{
  "postalCode": "10000",
  "weight": 2.4,
  "dimensions": {
    "length": 30,
    "width": 20,
    "height": 15
  },
  "serviceType": "regular"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `postalCode` | string | 5-digit Croatian postal code (e.g. `"10000"`) |
| `weight` | float | Parcel weight in **kilograms** (must be positive) |
| `dimensions.length` | float | Length in **centimeters** (must be positive) |
| `dimensions.width` | float | Width in **centimeters** (must be positive) |
| `dimensions.height` | float | Height in **centimeters** (must be positive) |
| `serviceType` | string | `"regular"` or `"priority"` |

**Response 200 OK:**

```json
{
  "price": 5.49,
  "currency": "EUR",
  "serviceType": "regular",
  "estimatedDeliveryDays": 3,
  "breakdown": {
    "base": 4.50,
    "weightSurcharge": 0.00,
    "dimensionalSurcharge": 0.00,
    "zoneSurcharge": 0.00,
    "priorityMultiplier": 1.0
  }
}
```

**Error responses** (RFC 7807 JSON — API Platform default):

| Status | Cause |
|--------|-------|
| `422` | Validation failure (negative weight, zero dimension, missing field) |
| `422` | Unknown postal code |
| `422` | Parcel exceeds service limits (weight or dimension) |
| `400` | Malformed JSON payload |

### Example Requests

**Regular quote — Zagreb (mainland):**
```bash
curl -X POST http://localhost:8000/api/shipping/quotes \
  -H "Content-Type: application/json" \
  -d '{"postalCode":"10000","weight":2.4,"dimensions":{"length":30,"width":20,"height":15},"serviceType":"regular"}'
```

**Priority quote — Hvar (island):**
```bash
curl -X POST http://localhost:8000/api/shipping/quotes \
  -H "Content-Type: application/json" \
  -d '{"postalCode":"21450","weight":1.0,"dimensions":{"length":20,"width":15,"height":10},"serviceType":"priority"}'
```

**Remote area — Otočac:**
```bash
curl -X POST http://localhost:8000/api/shipping/quotes \
  -H "Content-Type: application/json" \
  -d '{"postalCode":"53220","weight":5.0,"dimensions":{"length":40,"width":30,"height":20},"serviceType":"regular"}'
```

## Configuration Knobs

Edit `config/packages/shipping.yaml`:

```yaml
shipping:
  volumetric_divisor: 5000       # cm³ → kg divisor (use 6000 for some carriers)
  priority_multiplier: 1.5       # price multiplier for priority service
  regular_delivery_days_base: 3  # estimated delivery days for regular
  priority_delivery_days_base: 2 # estimated delivery days for priority
  max_weight:
    regular: 30                  # kg hard limit for regular service
    priority: 30                 # kg hard limit for priority service
  max_dimension:
    regular: 150                 # cm longest side limit for regular
    priority: 150                # cm longest side limit for priority
```

## Zone Definitions

Three shipping zones are supported:

| Zone | Surcharge | Examples |
|------|-----------|---------|
| `mainland` | €0.00 | Zagreb, Split, Rijeka, Osijek, Dubrovnik |
| `island` | €2.00 | Hvar, Krk, Pag, Korčula, Lastovo, Vis, Brač |
| `remote` | €1.50 | Otočac, Gospić, Ogulin, Slunj, mountain villages |

Zones and their surcharges are stored in the `shipping_zones` database table. To modify surcharges, update the table directly or edit the fixtures and reseed.

## Tariff Tables

Weight brackets per zone are stored in the `weight_tariffs` database table. The pricing model uses **tiered brackets** (not a flat per-kg rate):

| Bracket | Mainland base | Island/Remote base | Unit price |
|---------|--------------|-------------------|------------|
| 0–1 kg  | €3.00 | €4.00 | €0.00/kg |
| 1–2 kg  | €3.50 | €4.50 | €0.50/kg |
| 2–5 kg  | €4.50 | €5.50 | €0.30/kg |
| 5–10 kg | €5.40 | €6.40 | €0.20/kg |
| 10–30 kg | €7.40 | €8.40 | €0.15/kg |
| 30+ kg  | €10.40 | €11.40 | €0.10/kg |

**Chargeable weight** = `max(actualWeight, volumetricWeight)` where `volumetricWeight = (L × W × H) / volumetric_divisor`.

## Seeding Postal Codes

A representative subset of Croatian ZIP codes is seeded via fixtures:

```bash
# Reseed all fixtures (zones + tariffs + postal codes)
make fixtures

# Or with full DB reset:
make db-reset
```

For the **full Hrvatska Pošta dataset**, import via SQL or a custom console command:
```bash
php bin/console app:import-postal-codes path/to/hp_zipcodes.csv
```
*(Console command not yet implemented — add as a future enhancement.)*

## Development Commands

```bash
make help          # Show all available commands
make install       # Install Composer dependencies
make setup         # Full setup (install + migrate + fixtures)
make test          # Run all tests
make test-unit     # Run unit tests only
make test-api      # Run API functional tests only
make stan          # Run PHPStan static analysis (level 8)
make cs            # Check code style (dry-run)
make cs-fix        # Fix code style
make migrate       # Run database migrations
make fixtures      # Load data fixtures
make db-reset      # Drop, recreate DB, migrate, and seed
```

## Architecture

```
src/
├── Config/
│   └── ShippingConfiguration.php     ← Typed config value object
├── DataFixtures/
│   ├── ZoneFixtures.php              ← Shipping zones seed
│   ├── WeightTariffFixtures.php      ← Weight bracket tariffs seed
│   └── PostalCodeFixtures.php        ← Croatian ZIP codes seed
├── Dto/
│   ├── DimensionsInput.php           ← Nested dimensions DTO
│   ├── ShippingQuoteInput.php        ← API input DTO + ApiResource
│   ├── PriceBreakdown.php            ← Price breakdown DTO
│   └── ShippingQuoteOutput.php       ← API output DTO
├── Entity/
│   ├── PostalCode.php                ← ZIP → zone mapping
│   ├── ShippingZone.php              ← Zone with surcharge
│   └── WeightTariff.php             ← Weight bracket tariff
├── Exception/
│   ├── UnresolvablePostalCodeException.php  → HTTP 422
│   ├── OversizedParcelException.php         → HTTP 422
│   └── NoTariffFoundException.php           → HTTP 500
├── Repository/
│   ├── PostalCodeRepositoryInterface.php
│   ├── PostalCodeRepository.php
│   ├── WeightTariffRepositoryInterface.php
│   └── WeightTariffRepository.php
├── Service/
│   ├── ShippingPriceCalculatorInterface.php  ← Seam for carrier swap
│   └── ShippingPriceCalculator.php           ← Core pricing logic
└── State/
    └── ShippingQuoteProcessor.php    ← API Platform State Processor
```

The `ShippingPriceCalculatorInterface` is the **extension seam**: to integrate real carrier rates (HP Express, GLS, DPD, Overseas), implement the interface and swap the binding in `services.yaml` — the endpoint contract stays unchanged.

## OpenAPI / Swagger Docs

Auto-generated by API Platform. Available at:
- `GET /api` — JSON OpenAPI spec
- `GET /api/docs` — Swagger UI (HTML)

## Notes

- **Currency**: EUR (Croatia adopted the euro on 1 Jan 2023). Prices are currently VAT-exclusive — confirm with stakeholders before locking the contract.
- **Stateless**: No database writes on quote. Reads only zone/tariff configuration.
- **Rate limiting**: Consider applying Symfony RateLimiter on `/api/shipping/quotes` for untrusted clients.
