<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ShippingQuoteInput;
use App\Dto\ShippingQuoteOutput;

/**
 * Calculates a shipping price quote for a parcel.
 *
 * This interface is the seam for swapping in real carrier rate implementations
 * (e.g. HP Express, GLS, DPD) without changing the endpoint contract.
 */
interface ShippingPriceCalculatorInterface
{
    /**
     * @throws \App\Exception\UnresolvablePostalCodeException when the postal code is not found
     * @throws \App\Exception\OversizedParcelException when the parcel exceeds service limits
     * @throws \App\Exception\NoTariffFoundException when no tariff bracket is configured for the zone/weight
     */
    public function calculate(ShippingQuoteInput $input): ShippingQuoteOutput;
}
