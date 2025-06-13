<?php

namespace App\Controller;

use App\Entity\AdditionalModule;
use App\Service\AdditionalModuleService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/additional-modules')]
#[Security(name: 'Bearer')]
class AdditionalModuleController extends AbstractController
{
    public function __construct(
        private readonly AdditionalModuleService $additionalModuleService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Получить список всех дополнительных модулей',
        tags: ['AdditionalModule'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список дополнительных модулей',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name_module', type: 'string'),
                        new OA\Property(property: 'description_module', type: 'string'),
                        new OA\Property(property: 'offer_price', type: 'number'),
                        new OA\Property(property: 'purchase_price', type: 'number'),
                        new OA\Property(property: 'max_discount_percent', type: 'number'),
                        new OA\Property(property: 'name_product', type: 'string'),
                        new OA\Property(property: 'product_id', type: 'integer', nullable: true),
                    ]
                ))
            )
        ]
    )]
    #[OA\Parameter(name: 'product_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'name_module', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name_module', 'offer_price', 'purchase_price']))]
    #[OA\Parameter(name: 'direction', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']))]
    public function list(Request $request): JsonResponse
    {
        $filters = [];

        if ($request->query->get('product_id')) {
            $filters['product_id'] = (int)$request->query->get('product_id');
        }

        if ($request->query->get('name_module')) {
            $filters['name_module'] = $request->query->get('name_module');
        }

        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction');

        $modules = $this->additionalModuleService->getAllModules($filters, $sort, $direction);

        $data = array_map(fn(AdditionalModule $module) => [
            'id' => $module->getId(),
            'name_module' => $module->getNameModule(),
            'description_module' => $module->getDescriptionModule(),
            'offer_price' => $module->getOfferPrice(),
            'purchase_price' => $module->getPurchasePrice(),
            'max_discount_percent' => $module->getMaxDiscountPercent(),
            'product_id' => $module->getProduct()?->getId(),
            'name_product' => $module->getProduct()?->getNameProduct(),
        ], $modules);

        return $this->json($data);
    }


    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Создать дополнительный модуль',
        tags: ['AdditionalModule'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name_module', 'description_module', 'offer_price', 'purchase_price', 'max_discount_percent'],
                properties: [
                    new OA\Property(property: 'name_module', type: 'string'),
                    new OA\Property(property: 'description_module', type: 'string'),
                    new OA\Property(property: 'offer_price', type: 'number'),
                    new OA\Property(property: 'purchase_price', type: 'number'),
                    new OA\Property(property: 'max_discount_percent', type: 'number'),
                    new OA\Property(property: 'product_id', type: 'integer', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Модуль создан'),
            new OA\Response(response: 400, description: 'Некорректные данные')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        foreach (['name_module', 'description_module', 'offer_price', 'purchase_price', 'max_discount_percent'] as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing field: $field"], 400);
            }
        }

        $module = $this->additionalModuleService->createModule($data);

        return $this->json([
            'id' => $module->getId(),
            'name_module' => $module->getNameModule(),
            'description_module' => $module->getDescriptionModule(),
            'offer_price' => $module->getOfferPrice(),
            'purchase_price' => $module->getPurchasePrice(),
            'max_discount_percent' => $module->getMaxDiscountPercent(),
            'product_id' => $module->getProduct()?->getId(),
        ], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Обновить дополнительный модуль по ID',
        tags: ['AdditionalModule'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name_module', 'description_module', 'offer_price', 'purchase_price', 'max_discount_percent'],
                properties: [
                    new OA\Property(property: 'name_module', type: 'string'),
                    new OA\Property(property: 'description_module', type: 'string'),
                    new OA\Property(property: 'offer_price', type: 'number'),
                    new OA\Property(property: 'purchase_price', type: 'number'),
                    new OA\Property(property: 'max_discount_percent', type: 'number'),
                    new OA\Property(property: 'product_id', type: 'integer', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Модуль обновлен'),
            new OA\Response(response: 400, description: 'Некорректные данные'),
            new OA\Response(response: 404, description: 'Модуль не найден'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $module = $this->additionalModuleService->getModule($id);
        if (!$module) {
            return $this->json(['error' => 'Module not found'], 404);
        }

        foreach (['name_module', 'description_module', 'offer_price', 'purchase_price', 'max_discount_percent'] as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing field: $field"], 400);
            }
        }

        $updatedModule = $this->additionalModuleService->updateModule($module, $data);

        return $this->json([
            'id' => $updatedModule->getId(),
            'name_module' => $updatedModule->getNameModule(),
            'description_module' => $updatedModule->getDescriptionModule(),
            'offer_price' => $updatedModule->getOfferPrice(),
            'purchase_price' => $updatedModule->getPurchasePrice(),
            'max_discount_percent' => $updatedModule->getMaxDiscountPercent(),
            'product_id' => $updatedModule->getProduct()?->getId(),
        ]);
    }


    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Получить дополнительный модуль по ID',
        tags: ['AdditionalModule'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Модуль найден'),
            new OA\Response(response: 404, description: 'Модуль не найден'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $module = $this->additionalModuleService->getModule($id);

        if (!$module) {
            return $this->json(['error' => 'Module not found'], 404);
        }

        return $this->json([
            'id' => $module->getId(),
            'name_module' => $module->getNameModule(),
            'description_module' => $module->getDescriptionModule(),
            'offer_price' => $module->getOfferPrice(),
            'purchase_price' => $module->getPurchasePrice(),
            'max_discount_percent' => $module->getMaxDiscountPercent(),
            'product_id' => $module->getProduct()?->getId(),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Удалить дополнительный модуль по ID',
        tags: ['AdditionalModule'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Удалено успешно'),
            new OA\Response(response: 404, description: 'Модуль не найден'),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $module = $this->additionalModuleService->getModule($id);

        if (!$module) {
            return $this->json(['error' => 'Module not found'], 404);
        }

        $this->additionalModuleService->deleteModule($module);

        return new JsonResponse(null, 204);
    }
}
