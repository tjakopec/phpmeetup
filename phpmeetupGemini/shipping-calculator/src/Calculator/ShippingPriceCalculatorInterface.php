<?php

namespace App\Calculator;

use App\Dto\ShippingQuoteRequest;
use App\Dto\ShippingQuoteResponse;

interface ShippingPriceCalculatorInterface
{
    public function calculate(ShippingQuoteRequest $request): ShippingQuoteResponse;
}
