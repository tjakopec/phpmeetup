<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class Dimensions
{
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\Positive]
    public float $length;

    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\Positive]
    public float $width;

    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\Positive]
    public float $height;
}
