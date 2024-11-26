<?php

namespace App\Entity;

use App\Repository\DeliveryDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryDetailsRepository::class)]
class DeliveryDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $shippedQty = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShippedQty(): ?int
    {
        return $this->shippedQty;
    }

    public function setShippedQty(int $shippedQty): static
    {
        $this->shippedQty = $shippedQty;

        return $this;
    }
}
