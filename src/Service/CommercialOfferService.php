<?php

namespace App\Service;

use App\Entity\AdditionalModule;
use App\Entity\BaseLicense;
use App\Entity\CommercialOffers;
use App\Entity\CommercialOffersItems;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\DiscountLevelRepository;
use App\Repository\LicenseCompositionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommercialOfferService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenseCompositionRepository $licenseCompositionRepository,
        private DiscountLevelRepository $discountLevelRepository
    ) {
    }

    public function createCommercialOffer(User $user): CommercialOffers
    {
        $offer = new CommercialOffers();
        $offer->addUserId($user);
        $offer->setStatus(false);
        $offer->setCreatedAt(new \DateTimeImmutable());
        $offer->setTotalPrice(0);

        $this->entityManager->persist($offer);
        $this->entityManager->flush();

        return $offer;
    }

    public function addProductToOffer(
        CommercialOffers $offer,
        Product $product,
        ?BaseLicense $baseLicense = null,
        ?AdditionalModule $additionalModule = null,
        int $quantity = 1
    ): CommercialOffersItems {
        // Проверяем, есть ли уже такой продукт в предложении
        $existingItem = $this->findExistingItem($offer, $product, $baseLicense, $additionalModule);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
            $this->updateItemPrice($existingItem);
            $this->entityManager->flush();
            return $existingItem;
        }

        $item = new CommercialOffersItems();
        $item->setCommercialOfferId($offer);
        $item->setProduct($product);
        $item->setBaseLicense($baseLicense);
        $item->setAdditionalModule($additionalModule);
        $item->setQuantity($quantity);

        $this->updateItemPrice($item);
        $offer->addCommercialOffersItem($item);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        $this->recalculateTotalPrice($offer);

        return $item;
    }

    private function findExistingItem(
        CommercialOffers $offer,
        Product $product,
        ?BaseLicense $baseLicense,
        ?AdditionalModule $additionalModule
    ): ?CommercialOffersItems {
        foreach ($offer->getCommercialOffersItems() as $item) {
            if ($item->getProduct() === $product &&
                $item->getBaseLicense() === $baseLicense &&
                $item->getAdditionalModule() === $additionalModule) {
                return $item;
            }
        }
        return null;
    }

    private function updateItemPrice(CommercialOffersItems $item): void
    {
        $price = 0;

        if ($item->getBaseLicense()) {
            $price += $item->getBaseLicense()->getPurchasePriceLicense();
        }

        if ($item->getAdditionalModule()) {
            $price += $item->getAdditionalModule()->getPurchasePrice();
        }

        // Применяем скидки
        $discount = $this->calculateDiscount($item);
        if ($discount > 0) {
            $price = $price * (1 - $discount / 100);
            $item->setDiscount($discount);
        }

        $item->setPrice((int)round($price * $item->getQuantity()));
    }

    private function calculateDiscount(CommercialOffersItems $item): float
    {
        if (!$item->getProduct()) {
            return 0;
        }

        $discountLevels = $this->discountLevelRepository->findBy(['product' => $item->getProduct()]);
        $maxDiscount = 0;

        foreach ($discountLevels as $level) {
            if ($level->getDiscountPercent() > $maxDiscount) {
                $maxDiscount = $level->getDiscountPercent();
            }
        }

        return $maxDiscount;
    }

    private function recalculateTotalPrice(CommercialOffers $offer): void
    {
        $total = 0;
        foreach ($offer->getCommercialOffersItems() as $item) {
            $total += $item->getPrice();
        }

        $offer->setTotalPrice($total);
        $this->entityManager->flush();
    }

    public function getCompatibleModules(BaseLicense $baseLicense): array
    {
        return $this->licenseCompositionRepository->findCompatibleModules($baseLicense);
    }


    public function deleteCommercialOffer(int $id): void
    {
        $offer = $this->entityManager->getRepository(CommercialOffers::class)->find($id);

        if (!$offer) {
            throw new NotFoundHttpException('Коммерческое предложение не найдено');
        }

        $this->entityManager->remove($offer);
        $this->entityManager->flush();
    }
}