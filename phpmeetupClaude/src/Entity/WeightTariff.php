<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'weight_tariffs')]
class WeightTariff
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ShippingZone::class, inversedBy: 'weightTariffs')]
    #[ORM\JoinColumn(nullable: false)]
    private ShippingZone $zone;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 3)]
    private string $minWeight;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 3, nullable: true)]
    private ?string $maxWeight = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private string $basePrice;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 4)]
    private string $weightUnitPrice;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMinWeight(): float
    {
        return (float) $this->minWeight;
    }

    public function setMinWeight(float $minWeight): static
    {
        $this->minWeight = (string) $minWeight;

        return $this;
    }

    public function getMaxWeight(): ?float
    {
        return $this->maxWeight !== null ? (float) $this->maxWeight : null;
    }

    public function setMaxWeight(?float $maxWeight): static
    {
        $this->maxWeight = $maxWeight !== null ? (string) $maxWeight : null;

        return $this;
    }

    public function getBasePrice(): float
    {
        return (float) $this->basePrice;
    }

    public function setBasePrice(float $basePrice): static
    {
        $this->basePrice = (string) $basePrice;

        return $this;
    }

    public function getWeightUnitPrice(): float
    {
        return (float) $this->weightUnitPrice;
    }

    public function setWeightUnitPrice(float $weightUnitPrice): static
    {
        $this->weightUnitPrice = (string) $weightUnitPrice;

        return $this;
    }
}
