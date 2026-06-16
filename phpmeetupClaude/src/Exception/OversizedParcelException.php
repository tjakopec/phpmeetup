<?php

declare(strict_types=1);

namespace App\Exception;

final class OversizedParcelException extends \RuntimeException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            sprintf('Parcel exceeds the allowed limits for this service: %s', $reason),
        );
    }
}
