<?php

namespace App\Repository;

use App\Entity\BaseLicense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BaseLicense>
 */
class BaseLicenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseLicense::class);
    }

    public function findByFilters(array $filters = [], ?string $sort = null, ?string $direction = null): array
    {
        $qb = $this->createQueryBuilder('l');

        if (!empty($filters['name_license'])) {
            $qb->andWhere('LOWER(l.nameLicense) LIKE :name_license')
                ->setParameter('name_license', '%' . mb_strtolower($filters['name_license']) . '%');
        }

        if (!empty($filters['product_id'])) {
            $qb->leftJoin('l.product', 'p')
                ->andWhere('p.id = :product_id')
                ->setParameter('product_id', $filters['product_id']);
        }

        if ($sort && in_array($sort, ['name_license', 'price'], true)) {
            $sortField = match ($sort) {
                'name_license' => 'l.nameLicense',
                'price' => 'l.price',
                default => 'l.id'
            };

            $qb->orderBy($sortField, strtolower($direction) === 'desc' ? 'DESC' : 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}
