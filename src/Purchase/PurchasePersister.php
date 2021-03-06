<?php

namespace App\Purchase;

use App\Cart\CartService;
use DateTime;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class PurchasePersister
{

    protected $security;
    protected $cartService;
    protected $em;

    public function __construct(Security $security, CartService $cartService, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->cartService = $cartService;
        $this->em = $em;
    }

    public function storePruchase(Purchase $purchase)
    {

        // 6 Nous allons la lier avec l'utilisateur actuellement connecté (Sécurity)
        $purchase->setUser($this->security->getUser())
            ->setPurchasedAt(new DateTime())
            ->setTotal($this->cartService->getTotal());


        $this->em->persist($purchase);

        // 7 Nous allons la lier avec les produits qui sont dans le panier (CartService)

        foreach ($this->cartService->getDetailedCartItems() as $cartItem) {
            $purchaseItem = new PurchaseItem;
            $purchaseItem->setPurchase($purchase)
                ->setProduct($cartItem->product)
                ->setProductName($cartItem->product->getName())
                ->setQuantity($cartItem->qty)
                ->setProductPrice($cartItem->product->getPrice())
                ->setTotal($cartItem->getTotal());


            $this->em->persist($purchaseItem);
        }


        // 8 Nous allons enregistrer la commande (EntityManagerInterface)
        $this->em->flush();
    }
}
