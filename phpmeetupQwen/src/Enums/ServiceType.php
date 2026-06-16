<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceType: string
{
    case REGULAR = 'regular';
    case PRIORITY = 'priority';

    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular',
            self::PRIORITY => 'Priority',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match (strtolower($label)) {
            'regular' => self::REGULAR,
            'priority' => self::PRIORITY,
            default => throw new \InvalidArgumentException("Unknown service type: {$label}"),
        };
    }
}