<?php

namespace App\Entity;

use App\Repository\DeliveryDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryDetailsRepository::class)]
#[ORM\Table(name: "delivery_details")]
class DeliveryDetails
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: "deliveryDetails")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Product $product = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Delivery::class, inversedBy: "deliveryDetails")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Delivery $delivery = null;

    #[ORM\Column(type: "integer", nullable: false)]
    private int $shippedQty;

    // Getters and Setters

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getShippedQty(): int
    {
        return $this->shippedQty;
    }

    public function setShippedQty(int $shippedQty): self
    {
        $this->shippedQty = $shippedQty;

        return $this;
    }
}
