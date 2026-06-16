<?php

namespace App\Entity;

use App\Repository\TariffRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TariffRepository::class)]
class Tariff extends BaseEntity
{
    #[ORM\Column(type: 'float')]
    private float $minWeight = 0.0;

    #[ORM\Column(type: 'float')]
    private float $maxWeight = 0.0;

    #[ORM\Column(type: 'float')]
    private float $basePrice = 0.0;

    #[ORM\ManyToOne(targetEntity: ServiceType::class, inversedBy: 'tariffs')]
    #[ORM\JoinColumn(nullable: false)]
    private ServiceType $serviceType;

    public function __construct()
    {
        $this->serviceType = new ServiceType();
    }

    public function getMinWeight(): float
    {
        return $this->minWeight;
    }

    public function setMinWeight(float $minWeight): self
    {
        $this->minWeight = $minWeight;

        return $this;
    }

    public function getMaxWeight(): float
    {
        return $this->maxWeight;
    }

    public function setMaxWeight(float $maxWeight): self
    {
        $this->maxWeight = $maxWeight;

        return $this;
    }

    public function getBasePrice(): float
    {
        return $this->basePrice;
    }

    public function setBasePrice(float $basePrice): self
    {
        $this->basePrice = $basePrice;

        return $this;
    }

    public function getServiceType(): ServiceType
    {
        return $this->serviceType;
    }

    public function setServiceType(ServiceType $serviceType): self
    {
        $this->serviceType = $serviceType;

        return $this;
    }
}
