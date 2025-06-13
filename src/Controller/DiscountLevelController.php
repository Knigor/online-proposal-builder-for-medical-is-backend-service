<?php

namespace App\Controller;

use App\Entity\DiscountLevel;
use App\Entity\Product;
use App\Service\DiscountCalculatorService;
use App\Service\DiscountLevelService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/discount-levels')]
class DiscountLevelController extends AbstractController
{
    public function __construct(
        private DiscountLevelService $discountLevelService,
        private EntityManagerInterface $entityManager
    ) {
    }


    // считаем скидку
    #[Route('/{id}/calculate-discount', name: 'discount_level_calculate', methods: ['POST'])]
    #[OA\Post(
        path: '/api/discount-levels/{id}/calculate-discount',
        summary: 'Рассчитать скидку для уровня',
        tags: ['Discount Level'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Скидка рассчитана',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'discount_percent', type: 'number', format: 'float')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Уровень скидки не найден'),
            new OA\Response(response: 400, description: 'Не удалось рассчитать скидку'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function calculateDiscount(int $id): JsonResponse
    {
        $discountLevel = $this->discountLevelService->getDiscountLevel($id);

        if (!$discountLevel) {
            return $this->json(['error' => 'Discount level not found'], 404);
        }

        try {
            $this->discountLevelService->calculateAndSetDiscount($discountLevel);

            return $this->json([
                'id' => $discountLevel->getId(),
                'discount_percent' => $discountLevel->getDiscountPercent()
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('', name: 'discount_level_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/discount-levels',
        summary: 'Создать уровень скидки',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type'],
                properties: [
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'min_licenses', type: 'integer'),
                    new OA\Property(property: 'max_licenses', type: 'integer'),
                    new OA\Property(property: 'min_amount', type: 'integer'),
                    new OA\Property(property: 'max_amount', type: 'integer'),
                    new OA\Property(property: 'discount_percent', type: 'number', format: 'float'),
                    new OA\Property(property: 'product_id', type: 'integer')
                ]
            )
        ),
        tags: ['Discount Level'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Уровень скидки создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'type', type: 'string'),
                        new OA\Property(property: 'min_licenses', type: 'integer'),
                        new OA\Property(property: 'max_licenses', type: 'integer'),
                        new OA\Property(property: 'min_amount', type: 'integer'),
                        new OA\Property(property: 'max_amount', type: 'integer'),
                        new OA\Property(property: 'discount_percent', type: 'number', format: 'float'),
                        new OA\Property(property: 'product', type: 'object', nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Неверные параметры'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $type = $data['type'] ?? null;
        $minLicenses = $data['min_licenses'] ?? null;
        $maxLicenses = $data['max_licenses'] ?? null;
        $minAmount = $data['min_amount'] ?? null;
        $maxAmount = $data['max_amount'] ?? null;
        $discountLevelPercent = $data['discount_percent'] ?? null;
        $productId = $data['product_id'] ?? null;

        if (!$type) {
            return $this->json(['error' => 'Type is required'], 400);
        }

        $product = $productId ? $this->entityManager->getRepository(Product::class)->find($productId) : null;

        $discountLevel = $this->discountLevelService->createDiscountLevel(
            $type,
            $minLicenses,
            $maxLicenses,
            $minAmount,
            $maxAmount,
            $discountLevelPercent,
            $product
        );

        return $this->json([
            'id' => $discountLevel->getId(),
            'type' => $discountLevel->getType(),
            'min_licenses' => $discountLevel->getMinLicenses(),
            'max_licenses' => $discountLevel->getMaxLicenses(),
            'min_amount' => $discountLevel->getMinAmount(),
            'max_amount' => $discountLevel->getMaxAmount(),
            'discount_percent' => $discountLevel->getDiscountPercent(),
            'product' => $product ? [
                'id' => $product->getId(),
                'name' => $product->getNameProduct()
            ] : null
        ], 201);
    }

    #[Route('/{id}', name: 'discount_level_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/discount-levels/{id}',
        summary: 'Получить уровень скидки по ID',
        tags: ['Discount Level'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Уровень скидки найден',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'type', type: 'string'),
                        new OA\Property(property: 'min_licenses', type: 'integer'),
                        new OA\Property(property: 'max_licenses', type: 'integer'),
                        new OA\Property(property: 'min_amount', type: 'integer'),
                        new OA\Property(property: 'max_amount', type: 'integer'),
                        new OA\Property(property: 'discount_percent', type: 'number', format: 'float'),
                        new OA\Property(property: 'product', type: 'object', nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Уровень скидки не найден'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function get(int $id): JsonResponse
    {
        $discountLevel = $this->discountLevelService->getDiscountLevel($id);

        if (!$discountLevel) {
            return $this->json(['error' => 'Discount level not found'], 404);
        }

        $product = $discountLevel->getProduct();

        return $this->json([
            'id' => $discountLevel->getId(),
            'type' => $discountLevel->getType(),
            'min_licenses' => $discountLevel->getMinLicenses(),
            'max_licenses' => $discountLevel->getMaxLicenses(),
            'min_amount' => $discountLevel->getMinAmount(),
            'max_amount' => $discountLevel->getMaxAmount(),
            'discount_percent' => $discountLevel->getDiscountPercent(),
            'product' => $product ? [
                'id' => $product->getId(),
                'name' => $product->getNameProduct()
            ] : null
        ]);
    }

    #[Route('/{id}', name: 'discount_level_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/discount-levels/{id}',
        summary: 'Обновить уровень скидки',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'min_licenses', type: 'integer'),
                    new OA\Property(property: 'max_licenses', type: 'integer'),
                    new OA\Property(property: 'min_amount', type: 'integer'),
                    new OA\Property(property: 'max_amount', type: 'integer'),
                    new OA\Property(property: 'discount_percent', type: 'number', format: 'float'),
                    new OA\Property(property: 'product_id', type: 'integer')
                ]
            )
        ),
        tags: ['Discount Level'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Уровень скидки обновлен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'type', type: 'string'),
                        new OA\Property(property: 'min_licenses', type: 'integer'),
                        new OA\Property(property: 'max_licenses', type: 'integer'),
                        new OA\Property(property: 'min_amount', type: 'integer'),
                        new OA\Property(property: 'max_amount', type: 'integer'),
                        new OA\Property(property: 'discount_percent', type: 'number', format: 'float'),
                        new OA\Property(property: 'product', type: 'object', nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Уровень скидки не найден'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $discountLevel = $this->discountLevelService->getDiscountLevel($id);

        if (!$discountLevel) {
            return $this->json(['error' => 'Discount level not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['type'])) {
            $discountLevel->setType($data['type']);
        }

        if (isset($data['min_licenses'])) {
            $discountLevel->setMinLicenses($data['min_licenses']);
        }

        if (isset($data['max_licenses'])) {
            $discountLevel->setMaxLicenses($data['max_licenses']);
        }

        if (isset($data['min_amount'])) {
            $discountLevel->setMinAmount($data['min_amount']);
        }

        if (isset($data['max_amount'])) {
            $discountLevel->setMaxAmount($data['max_amount']);
        }

        if (isset($data['discount_percent'])) {
            $discountLevel->setDiscountPercent($data['discount_percent']);
        }

        if (isset($data['product_id'])) {
            $product = $this->entityManager->getRepository(Product::class)->find($data['product_id']);
            $discountLevel->setProduct($product);
        }

        $this->discountLevelService->updateDiscountLevel($discountLevel);

        $product = $discountLevel->getProduct();

        return $this->json([
            'id' => $discountLevel->getId(),
            'type' => $discountLevel->getType(),
            'min_licenses' => $discountLevel->getMinLicenses(),
            'max_licenses' => $discountLevel->getMaxLicenses(),
            'min_amount' => $discountLevel->getMinAmount(),
            'max_amount' => $discountLevel->getMaxAmount(),
            'discount_percent' => $discountLevel->getDiscountPercent(),
            'product' => $product ? [
                'id' => $product->getId(),
                'name' => $product->getNameProduct()
            ] : null
        ]);
    }

    #[Route('/{id}', name: 'discount_level_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/discount-levels/{id}',
        summary: 'Удалить уровень скидки',
        tags: ['Discount Level'],
        responses: [
            new OA\Response(response: 204, description: 'Уровень скидки удален'),
            new OA\Response(response: 404, description: 'Уровень скидки не найден'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $discountLevel = $this->discountLevelService->getDiscountLevel($id);

        if (!$discountLevel) {
            return $this->json(['error' => 'Discount level not found'], 404);
        }

        $this->discountLevelService->deleteDiscountLevel($discountLevel);

        return $this->json([], 204);
    }

    #[Route('', name: 'discount_level_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/discount-levels',
        summary: 'Получить список всех уровней скидок с возможностью фильтрации и поиска',
        tags: ['Discount Level'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                description: 'Фильтр по типу скидки',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'product_id',
                description: 'Фильтр по ID продукта',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по связанному продукту (названию)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'min_discount',
                description: 'Минимальный процент скидки',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', format: 'float')
            ),
            new OA\Parameter(
                name: 'max_discount',
                description: 'Максимальный процент скидки',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', format: 'float')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список уровней скидок',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'type', type: 'string'),
                            new OA\Property(property: 'min_licenses', type: 'integer'),
                            new OA\Property(property: 'max_licenses', type: 'integer'),
                            new OA\Property(property: 'min_amount', type: 'integer'),
                            new OA\Property(property: 'max_amount', type: 'integer'),
                            new OA\Property(property: 'discount_percent', type: 'number', format: 'float'),
                            new OA\Property(property: 'product', type: 'object', nullable: true)
                        ]
                    )
                )
            ),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $filters = [
            'type' => $request->query->get('type'),
            'product_id' => $request->query->get('product_id'),
            'search' => $request->query->get('search'),
            'min_discount' => $request->query->get('min_discount'),
            'max_discount' => $request->query->get('max_discount'),
        ];

        $discountLevels = $this->discountLevelService->getAllDiscountLevels($filters);

        $result = [];
        foreach ($discountLevels as $discountLevel) {
            $product = $discountLevel->getProduct();
            $result[] = [
                'id' => $discountLevel->getId(),
                'type' => $discountLevel->getType(),
                'min_licenses' => $discountLevel->getMinLicenses(),
                'max_licenses' => $discountLevel->getMaxLicenses(),
                'min_amount' => $discountLevel->getMinAmount(),
                'max_amount' => $discountLevel->getMaxAmount(),
                'discount_percent' => $discountLevel->getDiscountPercent(),
                'product' => $product ? [
                    'id' => $product->getId(),
                    'name' => $product->getNameProduct()
                ] : null
            ];
        }

        return $this->json($result);
    }
}