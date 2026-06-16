<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DimensionsDto
{
    public function __construct(
        #[Assert\NotBlank, Assert\Positive]
        public float $length = 0,
        #[Assert\NotBlank, Assert\Positive]
        public float $width = 0,
        #[Assert\NotBlank, Assert\Positive]
        public float $height = 0,
    ) {
    }
}
