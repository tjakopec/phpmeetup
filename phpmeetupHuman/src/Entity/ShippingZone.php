<?php

namespace App\Entity;

use App\Repository\ShippingZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShippingZoneRepository::class)]
class ShippingZone extends BaseEntity
{
    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column]
    private int $baseDeliveryDays = 0;

    #[ORM\Column]
    private float $zoneSurcharge = 0.00;

    /**
     * @var Collection<int, PostOffice>
     */
    #[ORM\OneToMany(mappedBy: 'shippingZone', targetEntity: PostOffice::class)]
    private Collection $postOffices;

    public function __construct()
    {
        $this->postOffices = new ArrayCollection();
    }

    /**
     * @return Collection<int, PostOffice>
     */
    public function getPostOffices(): Collection
    {
        return $this->postOffices;
    }

    public function addPostOffice(PostOffice $postOffice): static
    {
        if (!$this->postOffices->contains($postOffice)) {
            $this->postOffices->add($postOffice);
            $postOffice->setShippingZone($this);
        }

        return $this;
    }

    public function removePostOffice(PostOffice $postOffice): static
    {
        if ($this->postOffices->removeElement($postOffice)) {
            if ($postOffice->getShippingZone() === $this) {
                $postOffice->setShippingZone(null);
            }
        }

        return $this;
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

    public function getBaseDeliveryDays(): int
    {
        return $this->baseDeliveryDays;
    }

    public function setBaseDeliveryDays(int $baseDeliveryDays): static
    {
        $this->baseDeliveryDays = $baseDeliveryDays;

        return $this;
    }

    public function getZoneSurcharge(): float
    {
        return $this->zoneSurcharge;
    }

    public function setZoneSurcharge(float $zoneSurcharge): static
    {
        $this->zoneSurcharge = $zoneSurcharge;

        return $this;
    }
}
