<?php

namespace App\Service;

use App\Entity\BaseLicense;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class BaseLicenseService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createBaseLicense(array $data): BaseLicense
    {
        $license = new BaseLicense();

        $license->setNameLicense($data['name_license']);
        $license->setDescriptionLicense($data['description_license']);
        $license->setOfferPriceLicense($data['offer_price_license']);
        $license->setPurchasePriceLicense($data['purchase_price_license']);
        $license->setMaxDiscount($data['max_discount']);
        $license->setTypeLicense($data['type_license']);

        if (!empty($data['product_id'])) {
            $product = $this->entityManager->getRepository(Product::class)->find($data['product_id']);
            $license->setProduct($product);
        }

        $this->entityManager->persist($license);
        $this->entityManager->flush();

        return $license;
    }

    public function updateBaseLicense(BaseLicense $license, array $data): BaseLicense
    {
        $license->setNameLicense($data['name_license']);
        $license->setDescriptionLicense($data['description_license']);
        $license->setOfferPriceLicense($data['offer_price_license']);
        $license->setPurchasePriceLicense($data['purchase_price_license']);
        $license->setMaxDiscount($data['max_discount']);
        $license->setTypeLicense($data['type_license']);

        if (array_key_exists('product_id', $data)) {
            $product = $data['product_id']
                ? $this->entityManager->getRepository(Product::class)->find($data['product_id'])
                : null;
            $license->setProduct($product);
        }

        $this->entityManager->flush();

        return $license;
    }
}
