<?php

declare(strict_types=1);

namespace App\Exception;

final class UnresolvablePostalCodeException extends \RuntimeException
{
    public function __construct(string $postalCode)
    {
        parent::__construct(
            sprintf('Postal code "%s" is not recognized. Please provide a valid Croatian postal code.', $postalCode),
        );
    }
}
