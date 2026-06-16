<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Calculator\ShippingPriceCalculatorInterface;
use App\Dto\ShippingQuoteRequest;

class ShippingQuoteStateProcessor implements ProcessorInterface
{
    private ShippingPriceCalculatorInterface $calculator;

    public function __construct(ShippingPriceCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof ShippingQuoteRequest) {
            throw new \InvalidArgumentException('Data must be instance of ShippingQuoteRequest');
        }

        return $this->calculator->calculate($data);
    }
}
