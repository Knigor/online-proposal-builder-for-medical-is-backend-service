<?php

namespace App\Service;

use App\Entity\AdditionalModule;
use App\Entity\BaseLicense;
use App\Entity\CommercialOffers;
use App\Entity\CommercialOffersItemModule;
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
        array $additionalModules = [],
        int $quantity = 1
    ): CommercialOffersItems {
        $item = new CommercialOffersItems();
        $item->setCommercialOfferId($offer);
        $item->setProduct($product);
        $item->setBaseLicense($baseLicense);
        $item->setQuantity($quantity);

        // Добавляем все указанные модули
        foreach ($additionalModules as $module) {
            $itemModule = new CommercialOffersItemModule();
            $itemModule->setItem($item);
            $itemModule->setAdditionalModule($module);
            $item->addCommercialOffersItemModule($itemModule);
            $this->entityManager->persist($itemModule);
        }

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
        array $additionalModules = []
    ): ?CommercialOffersItems {
        foreach ($offer->getCommercialOffersItems() as $item) {
            // Проверяем совпадение продукта и лицензии
            if ($item->getProduct() !== $product || $item->getBaseLicense() !== $baseLicense) {
                continue;
            }

            // Получаем ID модулей текущего элемента
            $existingModuleIds = [];
            foreach ($item->getCommercialOffersItemModules() as $itemModule) {
                $existingModuleIds[] = $itemModule->getAdditionalModule()->getId();
            }


            // Получаем ID запрашиваемых модулей
            $requestedModuleIds = [];
            foreach ($additionalModules as $module) {
                $requestedModuleIds[] = $module->getId();
            }

            // Сортируем массивы для сравнения
            sort($existingModuleIds);
            sort($requestedModuleIds);

            // Если модули совпадают - возвращаем элемент
            if ($existingModuleIds === $requestedModuleIds) {
                return $item;
            }
        }

        return null;
    }

    private function compareModules(iterable $itemModules, array $inputModules): bool
    {
        $itemModuleIds = [];
        foreach ($itemModules as $itemModule) {
            $itemModuleIds[] = $itemModule->getId();
        }

        $inputModuleIds = array_map(fn($m) => $m->getId(), $inputModules);

        sort($itemModuleIds);
        sort($inputModuleIds);

        return $itemModuleIds === $inputModuleIds;
    }

    public function updateItemPrice(CommercialOffersItems $item): void
    {
        $price = 0;

        if ($item->getBaseLicense()) {
            $price += $item->getBaseLicense()->getPurchasePriceLicense();
        }

        foreach ($item->getCommercialOffersItemModules() as $itemModule) {
            $module = $itemModule->getAdditionalModule();
            if ($module) {
                $price += $module->getPurchasePrice();
            }
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

    public function recalculateTotalPrice(CommercialOffers $offer): void
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