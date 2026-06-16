<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ShippingQuoteInput;
use App\Dto\ShippingQuoteOutput;
use App\Service\ShippingPriceCalculatorInterface;

/**
 * @implements ProcessorInterface<ShippingQuoteInput, ShippingQuoteOutput>
 */
final class ShippingQuoteProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ShippingPriceCalculatorInterface $calculator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ShippingQuoteOutput
    {
        /** @var ShippingQuoteInput $data */
        return $this->calculator->calculate($data);
    }
}
