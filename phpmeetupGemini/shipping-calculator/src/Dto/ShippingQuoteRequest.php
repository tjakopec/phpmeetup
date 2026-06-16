<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\ShippingQuoteStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/shipping/quotes',
            processor: ShippingQuoteStateProcessor::class
        )
    ]
)]
class ShippingQuoteRequest
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^(10|20|21|22|23|31|32|33|34|35|40|42|43|44|47|48|49|51|52|53)\d{3}$/', message: 'Invalid Croatian postal code.')]
    public string $postalCode;

    #[Assert\NotBlank]
    #[Assert\Valid]
    public Dimensions $dimensions;

    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\Positive]
    public float $weight;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['regular', 'priority'], message: 'Choose a valid service type.')]
    public string $serviceType;
}
