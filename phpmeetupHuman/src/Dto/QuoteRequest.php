<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\QuoteProcessor;
use App\Validator\CroatianPostalCode;
use App\Validator\ServiceTypeExists;
use App\Validator\ShippingLimitsConstraint;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/shipping/quotes',
            formats: ['json' => ['application/json']],
            input: QuoteRequest::class,
            output: QuoteResponse::class,
            status: Response::HTTP_OK,
            processor: QuoteProcessor::class,
            normalizationContext: [
                'json_encode_options' => \JSON_PRESERVE_ZERO_FRACTION,
            ]
        ),
    ]
)]
#[ShippingLimitsConstraint]
class QuoteRequest
{
    #[Assert\NotBlank(message: 'Postal code is required.')]
    #[CroatianPostalCode]
    public string $postalCode = '';

    #[Assert\NotBlank(message: 'Service type is required.')]
    #[ServiceTypeExists]
    public string $serviceType = 'regular';

    #[Assert\NotBlank(message: 'Weight is required.')]
    #[Assert\Positive(message: 'Weight must be greater than zero.')]
    public float $weight = 0;

    #[Assert\Valid]
    private DimensionsDto $dimensions;

    public function __construct()
    {
        $this->dimensions = new DimensionsDto();
    }

    public function getDimensions(): DimensionsDto
    {
        return $this->dimensions;
    }

    public function setDimensions(DimensionsDto $dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    #[Assert\Callback]
    public function validateServiceLimits(ExecutionContextInterface $context): void
    {
        if ('regular' === $this->serviceType && $this->weight > 30) {
            $context->buildViolation('Regular service parcels cannot exceed 30 kg.')
                ->atPath('weight')
                ->addViolation();
        }

        if ('express' === $this->serviceType && ($this->dimensions->length > 150 || $this->dimensions->width > 150 || $this->dimensions->height > 150)) {
            $context->buildViolation('Express service dimensions cannot exceed 150cm x 150cm x 150cm.')
                ->atPath('serviceType')
                ->addViolation();
        }
    }
}
