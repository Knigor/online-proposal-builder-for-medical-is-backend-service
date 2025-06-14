<?php

namespace App\Service;

use App\Entity\DiscountLevel;
use App\Entity\Product;
use App\Repository\DiscountLevelRepository;
use App\Repository\LicenseCompositionRepository;
use Doctrine\ORM\EntityManagerInterface;

class DiscountLevelService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DiscountLevelRepository $discountLevelRepository,
        private LicenseCompositionRepository $licenseCompositionRepository
    ) {
    }

    public function createDiscountLevel(
        string $type,
        ?int $minLicenses,
        ?int $maxLicenses,
        ?int $minAmount,
        ?int $maxAmount,
        ?int $discountLevelPercent,
        ?Product $product
    ): DiscountLevel {
        $discountLevel = new DiscountLevel();
        $discountLevel->setType($type);
        $discountLevel->setMinLicenses($minLicenses);
        $discountLevel->setMaxLicenses($maxLicenses);
        $discountLevel->setMinAmount($minAmount);
        $discountLevel->setMaxAmount($maxAmount);
        $discountLevel->setDiscountPercent($discountLevelPercent);
        $discountLevel->setProduct($product);

        $this->entityManager->persist($discountLevel);
        $this->entityManager->flush();

        return $discountLevel;
    }

    public function getDiscountLevel(int $id): ?DiscountLevel
    {
        return $this->discountLevelRepository->find($id);
    }

    public function updateDiscountLevel(DiscountLevel $discountLevel): void
    {
        $this->entityManager->flush();
    }

    public function deleteDiscountLevel(DiscountLevel $discountLevel): void
    {
        $this->entityManager->remove($discountLevel);
        $this->entityManager->flush();
    }

    public function getAllDiscountLevels(array $filters = []): array
    {
        $queryBuilder = $this->discountLevelRepository->createQueryBuilder('dl')
            ->leftJoin('dl.product', 'p');

        if (!empty($filters['type'])) {
            $queryBuilder->andWhere('dl.type = :type')
                ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['product_id'])) {
            $queryBuilder->andWhere('dl.product = :product_id')
                ->setParameter('product_id', $filters['product_id']);
        }

        if (!empty($filters['search'])) {
            $queryBuilder->andWhere('p.nameProduct LIKE :search')
                ->setParameter('search', '%'.$filters['search'].'%');
        }

        if (!empty($filters['min_discount'])) {
            $queryBuilder->andWhere('dl.discount_percent >= :min_discount')
                ->setParameter('min_discount', $filters['min_discount']);
        }

        if (!empty($filters['max_discount'])) {
            $queryBuilder->andWhere('dl.discount_percent <= :max_discount')
                ->setParameter('max_discount', $filters['max_discount']);
        }

        return $queryBuilder->getQuery()->getResult();
    }


    // считаем скидки

    public function calculateAndSetDiscount(DiscountLevel $discountLevel): void
    {
        $product = $discountLevel->getProduct();
        if (!$product) {
            return;
        }

        // Получаем все композиции лицензий для данного продукта
        $compositions = $this->licenseCompositionRepository->findByProduct($product);

        if ($discountLevel->getType() === 'По количеству') {
            $totalLicenses = $this->calculateTotalLicenses($compositions);
            $discountPercent = $this->calculateDiscountByCount(
                $totalLicenses,
                $discountLevel->getMinLicenses(),
                $discountLevel->getMaxLicenses()
            );
        } else {
            $totalAmount = $this->calculateTotalAmount($compositions);
            $discountPercent = $this->calculateDiscountByAmount(
                $totalAmount,
                $discountLevel->getMinAmount(),
                $discountLevel->getMaxAmount()
            );
        }

        $discountLevel->setDiscountPercent($discountPercent);
        $this->entityManager->flush();
    }

    private function calculateTotalLicenses(array $compositions): int
    {

        $total = 0;
        foreach ($compositions as $composition) {
            if ($composition->getBaseLicense()) {
                $total++;
            }
            if ($composition->getAdditionalModule()) {
                $total++;
            }
        }
       // dd($total);
        return $total;

    }

    private function calculateTotalAmount(array $compositions): float
    {
        $total = 0.0;
        foreach ($compositions as $composition) {
            if ($composition->getBaseLicense()) {
                $total += $composition->getBaseLicense()->getPurchasePriceLicense();
            }
            if ($composition->getAdditionalModule()) {
                $total += $composition->getAdditionalModule()->getPurchasePrice();
            }
        }


        return $total;
    }

    private function calculateDiscountByCount(int $count, ?int $min, ?int $max): float
    {
        if ($min !== null && $count < $min) {
            return 0.0;
        }
        if ($max !== null && $count > $max) {
            return 0.0;
        }

        // Здесь можно реализовать любую логику расчета скидки
        // Например, линейное увеличение скидки от 5% до 20%
        $range = $max - $min;
        $position = $count - $min;
        return 5.0 + (15.0 * $position / $range);
    }

    private function calculateDiscountByAmount(float $amount, ?float $min, ?float $max): float
    {
        if ($min !== null && $amount < $min) {
            return 0.0;
        }
        if ($max !== null && $amount > $max) {
            return 0.0;
        }

        // Аналогичная логика для суммы
        $range = $max - $min;
        $position = $amount - $min;
        return 5.0 + (15.0 * $position / $range);
    }

}