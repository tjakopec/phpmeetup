<?php

namespace App\Validator;

use App\Dto\QuoteRequest;
use App\Entity\ServiceType;
use App\Repository\ServiceTypeRepository;
use App\Service\RequestDataStore;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ShippingLimitsConstraintValidator extends ConstraintValidator
{
    public function __construct(
        private ServiceTypeRepository $serviceTypeRepository,
        private RequestDataStore $dataStore,
    ) {
    }

    /**
     * @param mixed $value QuoteRequest object
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ShippingLimitsConstraint) {
            throw new UnexpectedTypeException($constraint, ShippingLimitsConstraint::class);
        }

        if (!$value instanceof QuoteRequest) {
            return;
        }

        $serviceType = $this->dataStore->get('quote_data') === true
                        ? $this->dataStore->get('quote_service_type')
                        : $this->serviceTypeRepository->findOneBy(['code' => $value->serviceType]);

        if (!$serviceType instanceof ServiceType) {
            return;
        }

        $maxWeight = $serviceType->getMaxWeight();

        if ($value->weight > $maxWeight) {
            $serviceName = $serviceType->getName();

            $this->context->buildViolation($constraint->weightMessage)
                ->setParameter('%service%', $serviceName)
                ->setParameter('%limit%', (string) $maxWeight)
                ->atPath('weight')
                ->addViolation();
        }

        $dimensions = $value->getDimensions();
        $longestSide = max($dimensions->length, $dimensions->width, $dimensions->height);
        $maxDimension = $serviceType->getMaxDimension();

        if ($longestSide > $maxDimension) {
            $serviceName = $serviceType->getName();

            $this->context->buildViolation($constraint->dimensionMessage)
                ->setParameter('%service%', $serviceName)
                ->setParameter('%limit%', (string) $maxDimension)
                ->atPath('dimensions')
                ->addViolation();
        }
    }
}
