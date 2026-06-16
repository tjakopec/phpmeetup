<?php

namespace App\Entity;

use App\Repository\PostOfficeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostOfficeRepository::class)]
class PostOffice extends BaseEntity
{
    #[ORM\Column(length: 50)]
    private string $postalCode = '';

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 3)]
    private string $currency = 'EUR';

    #[ORM\ManyToOne(inversedBy: 'postOffices')]
    private ?ShippingZone $shippingZone = null;

    public function getShippingZone(): ?ShippingZone
    {
        return $this->shippingZone;
    }

    public function setShippingZone(?ShippingZone $shippingZone): static
    {
        $this->shippingZone = $shippingZone;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }
}
