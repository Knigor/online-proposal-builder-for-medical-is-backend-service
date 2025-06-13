<?php

namespace App\Repository;

use App\Entity\BaseLicense;
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

    public function findCompatibleModules(BaseLicense $baseLicense): array
    {
        return $this->createQueryBuilder('lc')
            ->andWhere('lc.baseLicense = :baseLicense')
            ->andWhere('lc.compatible = true')
            ->setParameter('baseLicense', $baseLicense)
            ->join('lc.additionalModule', 'am')
            ->select('am')
            ->getQuery()
            ->getResult();
    }

    public function findGroupedByBaseLicense(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('lc')
            ->select(
                'bl.id AS base_license_id',
                'bl.nameLicense AS base_license_name',
                'am.id AS additional_module_id',
                'am.nameModule AS additional_module_name',
                'lc.required',
                'lc.compatible'
            )
            ->join('lc.baseLicense', 'bl')
            ->join('lc.additionalModule', 'am');

        if (isset($filters['required'])) {
            $qb->andWhere('lc.required = :required')
                ->setParameter('required', filter_var($filters['required'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['compatible'])) {
            $qb->andWhere('lc.compatible = :compatible')
                ->setParameter('compatible', filter_var($filters['compatible'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['base_license_name'])) {
            $qb->andWhere('LOWER(bl.nameLicense) LIKE :name')
                ->setParameter('name', '%' . mb_strtolower($filters['base_license_name']) . '%');
        }

        $sortField = match ($filters['sort'] ?? 'base_license_name') {
            'additional_module_name' => 'am.nameModule',
            default => 'bl.nameLicense',
        };

        $direction = strtoupper($filters['direction'] ?? 'ASC');
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        $qb->orderBy($sortField, $direction);

        $results = $qb->getQuery()->getArrayResult();

        // группировка
        $grouped = [];
        foreach ($results as $row) {
            $baseId = $row['base_license_id'];

            if (!isset($grouped[$baseId])) {
                $grouped[$baseId] = [
                    'base_license_id' => $baseId,
                    'base_license_name' => $row['base_license_name'],
                    'additional_modules' => [],
                ];
            }

            $description = match (true) {
                $row['required'] => 'входит',
                $row['compatible'] => 'сочетается',
                default => 'не сочетается'
            };

            $grouped[$baseId]['additional_modules'][] = [
                'id' => $row['additional_module_id'],
                'name' => $row['additional_module_name'],
                'relation' => $description,
            ];
        }

        return array_values($grouped);
    }





}