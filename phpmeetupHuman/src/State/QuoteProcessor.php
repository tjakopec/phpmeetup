<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\QuoteRequest;
use App\Dto\QuoteResponse;
use App\Service\ShippingPriceCalculatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * @implements ProcessorInterface<QuoteRequest, QuoteResponse>
 */
class QuoteProcessor implements ProcessorInterface
{
    public function __construct(
        private RateLimiterFactory $apiLimiter,
        private RequestStack $requestStack,
        private ShippingPriceCalculatorInterface $calculator,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): QuoteResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $limiter = $this->apiLimiter->create($request?->getClientIp() ?? 'anonymous');
        if (!$limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        return $this->calculator->calculate($data);
    }
}
