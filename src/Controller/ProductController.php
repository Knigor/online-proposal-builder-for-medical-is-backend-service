<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/products', name: 'api_products_')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository,
        private readonly SerializerInterface $serializer, // Внедрение сериализатора
    ) {}

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $products = $this->productRepository->findAll();

        // Сериализация с использованием группы 'product:read'
        $data = $this->serializer->serialize($products, 'json', ['groups' => 'product:read']);
        return new JsonResponse($data, 200, [], true); // Передаем строку JSON
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['userId'], $data['nameProduct'], $data['isActive'], $data['typeProduct'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        // Найти пользователя по userId (ID пользователя)
        $user = $this->em->getRepository(User::class)->find($data['userId']);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404); // Если пользователь не найден
        }

        $product = new Product();
        $product->setUserId($user);  // Передаем объект User, а не его ID
        $product->setNameProduct($data['nameProduct']);
        $product->setDiscriptionProduct($data['discriptionProduct'] ?? null);
        $product->setIsActive($data['isActive']);
        $product->setTypeProduct($data['typeProduct']);

        $this->em->persist($product);
        $this->em->flush();

        // Сериализация с использованием группы 'product:read'
        $data = $this->serializer->serialize($product, 'json', ['groups' => 'product:read']);
        return new JsonResponse($data, 201, [], true); // Передаем строку JSON
    }

    #[Route('/names', name: 'product_names', methods: ['GET'])]
    public function getProductNames(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->createQueryBuilder('p')
            ->select('p.id, p.nameProduct')
            ->getQuery()
            ->getResult();

        $formattedProducts = array_map(static function($product) {
            return [
                'id' => $product['id'],
                'name' => $product['nameProduct']
            ];
        }, $products);

        return $this->json([
            'product_names' => $formattedProducts
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        // Сериализация с использованием группы 'product:read'
        $data = $this->serializer->serialize($product, 'json', ['groups' => 'product:read']);
        return new JsonResponse($data, 200, [], true); // Передаем строку JSON
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Product $product): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['userIdId'])) {
            $product->setUserId($data['userIdId']);
        }
        if (isset($data['nameProduct'])) {
            $product->setNameProduct($data['nameProduct']);
        }
        if (array_key_exists('discriptionProduct', $data)) {
            $product->setDiscriptionProduct($data['discriptionProduct']);
        }
        if (isset($data['isActive'])) {
            $product->setIsActive($data['isActive']);
        }
        if (isset($data['typeProduct'])) {
            $product->setTypeProduct($data['typeProduct']);
        }

        $this->em->flush();

        // Сериализация с использованием группы 'product:read'
        $data = $this->serializer->serialize($product, 'json', ['groups' => 'product:read']);
        return new JsonResponse($data, 200, [], true); // Передаем строку JSON
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Product $product): JsonResponse
    {
        $this->em->remove($product);
        $this->em->flush();

        // Возвращаем сообщение об успешном удалении продукта
        return $this->json(['message' => 'Product deleted'], 200);
    }


}
