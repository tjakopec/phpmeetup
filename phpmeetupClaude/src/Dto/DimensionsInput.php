<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class DimensionsInput
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Positive(message: 'Dimension length must be a positive number.')]
        public readonly float $length = 0.0,

        #[Assert\NotNull]
        #[Assert\Positive(message: 'Dimension width must be a positive number.')]
        public readonly float $width = 0.0,

        #[Assert\NotNull]
        #[Assert\Positive(message: 'Dimension height must be a positive number.')]
        public readonly float $height = 0.0,
    ) {
    }
}
