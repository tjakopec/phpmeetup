<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'shipping_zones')]
class ShippingZone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private string $zoneSurcharge;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /** @var Collection<int, PostalCode> */
    #[ORM\OneToMany(targetEntity: PostalCode::class, mappedBy: 'zone')]
    private Collection $postalCodes;

    /** @var Collection<int, WeightTariff> */
    #[ORM\OneToMany(targetEntity: WeightTariff::class, mappedBy: 'zone')]
    private Collection $weightTariffs;

    public function __construct()
    {
        $this->postalCodes = new ArrayCollection();
        $this->weightTariffs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getZoneSurcharge(): float
    {
        return (float) $this->zoneSurcharge;
    }

    public function setZoneSurcharge(float $zoneSurcharge): static
    {
        $this->zoneSurcharge = (string) $zoneSurcharge;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /** @return Collection<int, PostalCode> */
    public function getPostalCodes(): Collection
    {
        return $this->postalCodes;
    }

    /** @return Collection<int, WeightTariff> */
    public function getWeightTariffs(): Collection
    {
        return $this->weightTariffs;
    }
}
