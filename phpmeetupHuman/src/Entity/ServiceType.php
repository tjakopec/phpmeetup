<?php

namespace App\Entity;

use App\Repository\ServiceTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceTypeRepository::class)]
class ServiceType extends BaseEntity
{
    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 100)]
    private string $name = '';

    #[ORM\Column(type: 'float')]
    private float $weightSurcharge = 0.0;

    #[ORM\Column(type: 'float')]
    private float $dimensionalSurcharge = 0.0;

    #[ORM\Column(type: 'float')]
    private float $priorityMultiplier = 0.0;

    #[ORM\Column(type: 'integer')]
    private int $volumeDivisor = 0;

    #[ORM\Column(type: 'integer')]
    private int $reducesEstimatedDeliveryDays = 0;

    #[ORM\Column(type: 'float')]
    private float $maxWeight = 0.0;

    #[ORM\Column(type: 'float')]
    private float $maxDimension = 0.0;

    /**
     * @var Collection<int, Tariff>
     */
    #[ORM\OneToMany(targetEntity: Tariff::class, mappedBy: 'serviceType', cascade: ['persist', 'remove'])]
    private Collection $tariffs;

    public function __construct()
    {
        $this->tariffs = new ArrayCollection();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getWeightSurcharge(): float
    {
        return $this->weightSurcharge;
    }

    public function setWeightSurcharge(float $weightSurcharge): self
    {
        $this->weightSurcharge = $weightSurcharge;

        return $this;
    }

    public function getDimensionalSurcharge(): float
    {
        return $this->dimensionalSurcharge;
    }

    public function setDimensionalSurcharge(float $dimensionalSurcharge): self
    {
        $this->dimensionalSurcharge = $dimensionalSurcharge;

        return $this;
    }

    public function getPriorityMultiplier(): float
    {
        return $this->priorityMultiplier;
    }

    public function setPriorityMultiplier(float $priorityMultiplier): self
    {
        $this->priorityMultiplier = $priorityMultiplier;

        return $this;
    }

    public function getVolumeDivisor(): int
    {
        return $this->volumeDivisor;
    }

    public function setVolumeDivisor(int $volumeDivisor): self
    {
        $this->volumeDivisor = $volumeDivisor;

        return $this;
    }

    public function getReducesEstimatedDeliveryDays(): int
    {
        return $this->reducesEstimatedDeliveryDays;
    }

    public function setReducesEstimatedDeliveryDays(int $reducesEstimatedDeliveryDays): self
    {
        $this->reducesEstimatedDeliveryDays = $reducesEstimatedDeliveryDays;

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

    public function getMaxDimension(): float
    {
        return $this->maxDimension;
    }

    public function setMaxDimension(float $maxDimension): self
    {
        $this->maxDimension = $maxDimension;

        return $this;
    }

    /**
     * @return Collection<int, Tariff>
     */
    public function getTariffs(): Collection
    {
        return $this->tariffs;
    }

    public function addTariff(Tariff $tariff): self
    {
        if (!$this->tariffs->contains($tariff)) {
            $this->tariffs->add($tariff);
            $tariff->setServiceType($this);
        }

        return $this;
    }

    public function removeTariff(Tariff $tariff): self
    {
        if ($this->tariffs->removeElement($tariff)) {
            // Postavi na null ako se poništava relacija (ovisno o nullable postavkama u Tariff)
            if ($tariff->getServiceType() === $this) {
                // Ako ne želiš dopustiti siročad (orphanRemoval), ovdje se obično odradi setServiceType(null);
            }
        }

        return $this;
    }
}
