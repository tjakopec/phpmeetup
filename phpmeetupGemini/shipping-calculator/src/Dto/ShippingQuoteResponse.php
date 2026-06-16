<?php

namespace App\Dto;

class ShippingQuoteResponse
{
    public float $totalPrice;
    public int $estimatedDeliveryDays;
    public PriceBreakdown $breakdown;
    public float $chargeableWeight;
}
