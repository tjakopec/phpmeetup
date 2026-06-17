<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\ShippingQuoteProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'ShippingQuote',
    operations: [
        new Post(
            uriTemplate: '/shipping/quotes',
            output: ShippingQuoteOutput::class,
            processor: ShippingQuoteProcessor::class,
            provider: null, 
            openapiContext: [
                'summary' => 'Calculate a shipping price quote',
                'description' => 'Returns a shipping price quote for a parcel within Croatia.',
                'tags' => ['Shipping'],
            ],
        ),
    ],
    formats: ['json' => ['application/json']],
)]
final class ShippingQuoteInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'Postal code is required.')]
        #[Assert\Regex(
            pattern: '/^\d{5}$/',
            message: 'Postal code must be exactly 5 digits.',
        )]
        public readonly string $postalCode = '',

        #[Assert\NotNull(message: 'Weight is required.')]
        #[Assert\Positive(message: 'Weight must be a positive number.')]
        public readonly float $weight = 0.0,

        #[Assert\NotNull(message: 'Dimensions are required.')]
        #[Assert\Valid]
        public readonly DimensionsInput $dimensions = new DimensionsInput(),

        #[Assert\NotBlank(message: 'Service type is required.')]
        #[Assert\Choice(
            choices: ['regular', 'priority'],
            message: 'Service type must be either "regular" or "priority".',
        )]
        public readonly string $serviceType = 'regular',
    ) {
    }
}
