<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class CroatianPostalCode extends Constraint
{
    public string $message = 'The postal code "{{ value }}" is not a recognized Croatian postal code.';

    public function validatedBy(): string
    {
        return CroatianPostalCodeValidator::class;
    }
}
