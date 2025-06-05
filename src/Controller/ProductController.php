<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;


#[Route('/api/products')]
#[Security(name: 'Bearer')]
class ProductController extends AbstractController
{
    private ProductService $productService;
    private EntityManagerInterface $entityManager;

    public function __construct(ProductService $productService, EntityManagerInterface $entityManager)
    {
        $this->productService = $productService;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'product_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/products',
        summary: 'Получить список всех продуктов',
        tags: ['Product'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список продуктов',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name_product', type: 'string'),
                            new OA\Property(property: 'discription_product', type: 'string'),
                            new OA\Property(property: 'user_id', type: 'integer', nullable: true)
                        ]
                    )
                )
            ),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function list(): JsonResponse
    {
        $products = $this->productService->getAllProducts();

        $data = array_map(function (Product $product) {
            return [
                'id' => $product->getId(),
                'name_product' => $product->getNameProduct(),
                'discription_product' => $product->getDiscriptionProduct(),
                'user_id' => $product->getUserProduct()?->getId(),
            ];
        }, $products);

        return $this->json($data);
    }

    #[Route('', name: 'product_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/products',
        summary: 'Создать продукт',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name_product', 'discription_product', 'user_id'],
                properties: [
                    new OA\Property(property: 'name_product', type: 'string'),
                    new OA\Property(property: 'discription_product', type: 'string'),
                    new OA\Property(property: 'user_id', type: 'integer')
                ]
            )
        ),
        tags: ['Product'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Продукт создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name_product', type: 'string'),
                        new OA\Property(property: 'discription_product', type: 'string'),
                        new OA\Property(property: 'user_id', type: 'integer')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name_product'] ?? null;
        $description = $data['discription_product'] ?? null;
        $userId = $data['user_id'] ?? null;

        if (!$name || !$description) {
            return $this->json(['error' => 'Missing parameters'], 400);
        }

        $product = $this->productService->createProduct($name, $description, $userId);

        return $this->json([
            'id' => $product->getId(),
            'name_product' => $product->getNameProduct(),
            'discription_product' => $product->getDiscriptionProduct(),
            'user_id' => $product->getUserProduct()?->getId(),
        ], 201);
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Получить один продукт по ID',
        tags: ['Product'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Продукт найден',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name_product', type: 'string'),
                        new OA\Property(property: 'discription_product', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Продукт не найден'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        return $this->json([
            'id' => $product->getId(),
            'name_product' => $product->getNameProduct(),
            'discription_product' => $product->getDiscriptionProduct(),
        ]);
    }

    #[Route('/{id}', name: 'product_update', methods: ['PUT', 'PATCH'])]
    #[OA\Patch(
        path: '/api/products/{id}',
        summary: 'Обновить продукт по ID',
        tags: ['Product'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name_product', type: 'string'),
                    new OA\Property(property: 'discription_product', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Продукт обновлён'),
            new OA\Response(response: 404, description: 'Продукт не найден'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $product = $this->productService->getProduct($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['name_product'])) {
            $product->setNameProduct($data['name_product']);
        }
        if (isset($data['discription_product'])) {
            $product->setDiscriptionProduct($data['discription_product']);
        }

        $this->productService->updateProduct($product);

        return $this->json([
            'id' => $product->getId(),
            'name_product' => $product->getNameProduct(),
            'discription_product' => $product->getDiscriptionProduct(),
        ]);
    }

    #[Route('/{id}', name: 'product_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/products/{id}',
        summary: 'Удалить продукт по ID',
        tags: ['Product'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Продукт удалён'),
            new OA\Response(response: 404, description: 'Продукт не найден'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $this->productService->deleteProduct($product);

        return $this->json(['message' => 'Product deleted successfully']);
    }
}
