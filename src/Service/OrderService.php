<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createOrder($user, $panier, $paymentMethod): Order
    {
        $order = (new Order())
            ->setUser($user)
            ->setRef('Com:' . uniqid())
            ->setPaymentMethod($paymentMethod)
            ->setType('commande')
            ->setPaymentDate(new \DateTimeImmutable())
            ->setPaymentStatus('En Cours de traitement')
            ->setDate(new \DateTimeImmutable())
            ->setStatus('En Attente');

        $totalAmount = 0;

        foreach ($panier as $productId => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);
            if (!$product) {
                throw new \Exception("Product with ID $productId not found.");
            }

            $productDetails = $this->calculateProductDetails($product, $quantity);
            $totalAmount += $productDetails['total'];

            $orderDetail = (new OrderDetails())
                ->setOrder($order)
                ->setProduct($product)
                ->setQuantity($quantity)
                ->setPrice($productDetails['priceWithTax']);

            $this->entityManager->persist($orderDetail);
        }

        $order->setTotal($totalAmount);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    private function calculateProductDetails($product, $quantity): array
    {
        $taxRate = $product->getTax()?->getRate() ?? 0;
        $priceWithTax = $product->getPrice() * (1 + $taxRate / 100);
        $total = $priceWithTax * $quantity;

        return ['total' => $total, 'priceWithTax' => $priceWithTax];
    }
}
