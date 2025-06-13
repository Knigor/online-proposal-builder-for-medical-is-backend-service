<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    private EntityManagerInterface $entityManager;
    private ProductRepository $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    public function createProduct(string $name, string $description, int $userId): Product
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $product = new Product();
        $product->setNameProduct($name);
        $product->setDiscriptionProduct($description);
        $product->setUserProduct($user);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function getProduct(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function updateProduct(Product $product): void
    {
        $this->entityManager->flush();
    }

    public function deleteProduct(Product $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    public function getAllProducts(
        ?string $search = null,
        array $filters = [],
        ?string $sort = null,
        ?string $direction = null
    ): array {
        $qb = $this->productRepository->createQueryBuilder('p');

        if ($search) {
            $qb->andWhere('p.nameProduct LIKE :search OR p.discriptionProduct LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if (isset($filters['user_id'])) {
            $qb->andWhere('p.userProduct = :user_id')
                ->setParameter('user_id', $filters['user_id']);
        }

        // Сортировка
        if ($sort && $sort === 'name_product') {
            $field = match ($sort) {
                'name_product' => 'p.nameProduct',
                default => 'p.id',
            };

            $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

            $qb->orderBy($field, $direction);
        }

        return $qb->getQuery()->getResult();
    }


}
