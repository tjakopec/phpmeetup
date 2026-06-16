<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ShippingLimitsConstraint extends Constraint
{
    public string $weightMessage = 'The "%service%" service does not accept parcels heavier than %limit% kg.';
    public string $dimensionMessage = 'The "%service%" service does not accept parcels with the longest side exceeding %limit% cm.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
