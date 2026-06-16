<?php

declare(strict_types=1);

namespace App\Exceptions;

final class DomainException extends ShippingException
{
    public function __construct(string $message = '', int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}