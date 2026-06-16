<?php

namespace App\Service;

use App\Dto\QuoteRequest;
use App\Dto\QuoteResponse;

interface ShippingPriceCalculatorInterface
{
    /**
     * Calculates the shipping cost based on the request.
     */
    public function calculate(QuoteRequest $request): QuoteResponse;
}
