<?php

namespace App\Repository;

use App\Entity\LicenseComposition;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LicenseCompositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenseComposition::class);
    }

    public function findByProduct(Product $product): array
    {
        return $this->createQueryBuilder('lc')
            ->leftJoin('lc.baseLicense', 'bl')
            ->leftJoin('lc.additionalModule', 'am')
            ->where('bl.product = :product OR am.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getResult();
    }
}