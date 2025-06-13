<?php

namespace App\Service;

use App\Entity\AdditionalModule;
use App\Entity\Product;
use App\Repository\AdditionalModuleRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdditionalModuleService
{
    private EntityManagerInterface $entityManager;
    private AdditionalModuleRepository $additionalModuleRepository;

    public function __construct(EntityManagerInterface $entityManager, AdditionalModuleRepository $additionalModuleRepository)
    {
        $this->entityManager = $entityManager;
        $this->additionalModuleRepository = $additionalModuleRepository;
    }

    public function createModule(array $data): AdditionalModule
    {
        $product = null;
        if (!empty($data['product_id'])) {
            $product = $this->entityManager->getRepository(Product::class)->find($data['product_id']);
        }

        $module = new AdditionalModule();
        $module->setNameModule($data['name_module']);
        $module->setDescriptionModule($data['description_module']);
        $module->setOfferPrice($data['offer_price']);
        $module->setPurchasePrice($data['purchase_price']);
        $module->setMaxDiscountPercent($data['max_discount_percent']);
        $module->setProduct($product);

        $this->entityManager->persist($module);
        $this->entityManager->flush();

        return $module;
    }

    public function getModule(int $id): ?AdditionalModule
    {
        return $this->additionalModuleRepository->find($id);
    }

    public function updateModule(AdditionalModule $module, array $data): AdditionalModule
    {
        $module->setNameModule($data['name_module']);
        $module->setDescriptionModule($data['description_module']);
        $module->setOfferPrice($data['offer_price']);
        $module->setPurchasePrice($data['purchase_price']);
        $module->setMaxDiscountPercent($data['max_discount_percent']);

        if (array_key_exists('product_id', $data)) {
            $product = $data['product_id']
                ? $this->entityManager->getRepository(Product::class)->find($data['product_id'])
                : null;
            $module->setProduct($product);
        }

        $this->entityManager->flush();

        return $module;
    }

    public function deleteModule(AdditionalModule $module): void
    {
        $this->entityManager->remove($module);
        $this->entityManager->flush();
    }

    public function getAllModules(array $filters = [], ?string $sort = null, ?string $direction = null): array
    {
        $qb = $this->additionalModuleRepository->createQueryBuilder('m')
            ->leftJoin('m.product', 'p');

        if (!empty($filters['product_id'])) {
            $qb->andWhere('p.id = :product_id')
                ->setParameter('product_id', $filters['product_id']);
        }

        if (!empty($filters['name_module'])) {
            $qb->andWhere('m.nameModule LIKE :name_module')
                ->setParameter('name_module', '%' . $filters['name_module'] . '%');
        }

        // Сортировка
        if ($sort && in_array($sort, ['name_module', 'offer_price', 'purchase_price'], true)) {
            $sortField = match ($sort) {
                'name_module' => 'm.nameModule',
                'offer_price' => 'm.offerPrice',
                'purchase_price' => 'm.purchasePrice',
                default => 'm.id'
            };

            $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

            $qb->orderBy($sortField, $direction);
        }

        return $qb->getQuery()->getResult();
    }

}
