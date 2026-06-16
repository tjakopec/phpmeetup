<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ServiceTypeExists extends Constraint
{
    public string $message = 'The service type "%value%" is not supported.';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
