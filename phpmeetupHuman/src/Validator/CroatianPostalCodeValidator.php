<?php

namespace App\Validator;

use App\Repository\PostOfficeRepository;
use App\Service\RequestDataStore;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CroatianPostalCodeValidator extends ConstraintValidator
{
    public function __construct(
        private PostOfficeRepository $postOfficeRepository,
        private RequestDataStore $dataStore,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CroatianPostalCode) {
            throw new UnexpectedTypeException($constraint, CroatianPostalCode::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', 'Invalid Type')
                ->addViolation();

            return;
        }

        $postalCodeString = (string) $value;

        $postOffice = $this->dataStore->get('quote_data') === true
                            ? $this->dataStore->get('quote_post_office')
                            : $this->postOfficeRepository->findOneBy(['postalCode' => $postalCodeString]);

        if (null === $postOffice) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $postalCodeString)
                ->addViolation();
        }
    }
}
