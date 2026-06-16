<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

abstract class ShippingException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}