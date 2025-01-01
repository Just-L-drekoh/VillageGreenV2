<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Delivery;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createOrderWithDelivery(User $user, array $panier, string $paymentMethod): Order
    {
        $order = (new Order())
            ->setUser($user)
            ->setRef(uniqid(true))
            ->setPaymentMethod($paymentMethod)
            ->setType('commande')
            ->setPaymentDate(new \DateTimeImmutable())
            ->setPaymentStatus('En attente de validation')
            ->setDate(new \DateTimeImmutable())
            ->setStatus('En cours de traitement');

        $totalAmount = 0;

        foreach ($panier as $productId => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);

            if (!$product) {
                throw new \Exception("Le produit avec l'ID $productId n'a pas été trouvé.");
            }

            if ($product->getStock() < $quantity) {
                throw new \Exception("Stock insuffisant pour le produit {$product->getName()}");
            }

            $taxRate = $product->getTax()?->getRate() ?? 0;
            $priceWithTax = $product->getPrice() * (1 + $taxRate / 100);
            $total = $priceWithTax * $quantity;

            $totalAmount += $total;


            $orderDetail = (new OrderDetails())
                ->setOrder($order)
                ->setProduct($product)
                ->setQuantity($quantity)
                ->setPrice($priceWithTax);


            $product->setStock($product->getStock() - $quantity);

            $this->entityManager->persist($orderDetail);
            $this->entityManager->persist($product);
        }

        $order->setTotal($totalAmount);


        $delivery = new Delivery();
        $delivery->setOrd($order)
            ->setDate(new \DateTimeImmutable())
            ->setNote('Livraison en cours de traitement');


        $this->entityManager->persist($order);
        $this->entityManager->persist($delivery);
        $this->entityManager->flush();

        return $order;
    }
}
