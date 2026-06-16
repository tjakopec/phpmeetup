<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'postal_codes')]
class PostalCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 5, unique: true)]
    private string $code;

    #[ORM\Column(type: 'string', length: 100)]
    private string $city;

    #[ORM\ManyToOne(targetEntity: ShippingZone::class, inversedBy: 'postalCodes')]
    #[ORM\JoinColumn(nullable: false)]
    private ShippingZone $zone;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZone(): ShippingZone
    {
        return $this->zone;
    }

    public function setZone(ShippingZone $zone): static
    {
        $this->zone = $zone;

        return $this;
    }
}
