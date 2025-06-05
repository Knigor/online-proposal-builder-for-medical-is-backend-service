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

    public function getAllModules(): array
    {
        return $this->additionalModuleRepository->findAll();
    }
}
