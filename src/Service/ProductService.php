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

    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }
}
