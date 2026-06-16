<?php

namespace App\Validator;

use App\Repository\ServiceTypeRepository;
use App\Service\RequestDataStore;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ServiceTypeExistsValidator extends ConstraintValidator
{
    public function __construct(
        private ServiceTypeRepository $serviceTypeRepository,
        private RequestDataStore $dataStore,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ServiceTypeExists) {
            throw new UnexpectedTypeException($constraint, ServiceTypeExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $serviceType = $this->dataStore->get('quote_data') === true
                            ? $this->dataStore->get('quote_service_type')
                            : $this->serviceTypeRepository->findOneBy(['code' => $value]);

        if (null === $serviceType) {
            $displayValue = is_scalar($value) ? (string) $value : 'unknown';

            $this->context->buildViolation($constraint->message)
                ->setParameter('%value%', $displayValue)
                ->addViolation();
        }
    }
}
