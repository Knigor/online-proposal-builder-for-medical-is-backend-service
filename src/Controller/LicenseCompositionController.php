<?php

namespace App\Controller;

use App\Entity\LicenseComposition;
use App\Entity\BaseLicense;
use App\Entity\AdditionalModule;
use App\Service\LicenseCompositionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/license-compositions')]
class LicenseCompositionController extends AbstractController
{
    public function __construct(
        private LicenseCompositionService $licenseCompositionService,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'license_composition_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/license-compositions',
        summary: 'Создать связи лицензии и модулей',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['base_license_id', 'modules'],
                properties: [
                    new OA\Property(property: 'base_license_id', type: 'integer'),
                    new OA\Property(
                        property: 'modules',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'module_id', type: 'integer'),
                                new OA\Property(property: 'required', type: 'boolean'),
                                new OA\Property(property: 'compatible', type: 'boolean')
                            ]
                        )
                    )
                ]
            )
        ),
        tags: ['License Composition'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Связи созданы',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'required', type: 'boolean'),
                            new OA\Property(property: 'compatible', type: 'boolean'),
                            new OA\Property(property: 'base_license', type: 'object'),
                            new OA\Property(property: 'additional_module', type: 'object')
                        ]
                    )
                )
            ),
            new OA\Response(response: 400, description: 'Неверные параметры'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $baseLicenseId = $data['base_license_id'] ?? null;
        $modules = $data['modules'] ?? [];

        if ($baseLicenseId === null || empty($modules)) {
            return $this->json(['error' => 'Missing required parameters'], 400);
        }

        $baseLicense = $this->entityManager->getRepository(BaseLicense::class)->find($baseLicenseId);
        if (!$baseLicense) {
            return $this->json(['error' => 'Base license not found'], 404);
        }

        $results = [];
        foreach ($modules as $moduleData) {
            $moduleId = $moduleData['module_id'] ?? null;
            $required = $moduleData['required'] ?? false;
            $compatible = $moduleData['compatible'] ?? true;

            if ($moduleId === null) {
                continue;
            }

            $additionalModule = $this->entityManager->getRepository(AdditionalModule::class)->find($moduleId);
            if (!$additionalModule) {
                continue;
            }

            $licenseComposition = $this->licenseCompositionService->createLicenseComposition(
                $required,
                $compatible,
                $baseLicense,
                $additionalModule
            );

            $results[] = $licenseComposition;
        }

        return new JsonResponse(
            $this->serializer->serialize($results, 'json', [
                'groups' => ['composition:read', 'license:read', 'module:read']
            ]),
            201,
            [],
            true
        );
    }
    #[Route('/{id}', name: 'license_composition_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/license-compositions/{id}',
        summary: 'Получить связи лицензии по ID',
        tags: ['License Composition'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Связи найдены',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'base_license_id', type: 'integer'),
                            new OA\Property(property: 'base_license_name', type: 'string'),
                            new OA\Property(
                                property: 'additional_modules',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer'),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'relation', type: 'string')
                                    ]
                                )
                            )
                        ]
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Лицензия не найдена'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    #[Route('/{id}', name: 'license_composition_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        // Получаем базовую лицензию
        $baseLicense = $this->entityManager->getRepository(BaseLicense::class)->find($id);

        if (!$baseLicense) {
            return $this->json(['error' => 'Base license not found'], 404);
        }

        // Получаем все связи для этой лицензии
        $compositions = $this->entityManager->getRepository(LicenseComposition::class)
            ->findBy(['baseLicense' => $id]);

        // Формируем ответ
        $response = [
            'base_license_id' => $baseLicense->getId(),
            'base_license_name' => $baseLicense->getNameLicense(),
            'additional_modules' => []
        ];

        foreach ($compositions as $composition) {
            $additionalModule = $composition->getAdditionalModule();

            $relation = 'не сочетается';
            if ($composition->isRequired()) {
                $relation = 'входит';
            } elseif ($composition->isCompatible()) {
                $relation = 'сочетается';
            }

            $response['additional_modules'][] = [
                'id' => $additionalModule->getId(), // Используем ID модуля, а не связи
                'name' => $additionalModule->getNameModule(),
                'price' => $additionalModule->getOfferPrice(),
                'relation' => $relation
            ];
        }

        return $this->json($response);
    }

    #[Route('/{id}', name: 'license_composition_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/license-compositions/{id}',
        summary: 'Обновить связь лицензии и модулей',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['base_license_id', 'modules'],
                properties: [
                    new OA\Property(property: 'base_license_id', type: 'integer'),
                    new OA\Property(
                        property: 'modules',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'module_id', type: 'integer'),
                                new OA\Property(property: 'required', type: 'boolean'),
                                new OA\Property(property: 'compatible', type: 'boolean')
                            ]
                        )
                    )
                ]
            )
        ),
        tags: ['License Composition'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Связи обновлены',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'required', type: 'boolean'),
                            new OA\Property(property: 'compatible', type: 'boolean'),
                            new OA\Property(property: 'base_license', type: 'object'),
                            new OA\Property(property: 'additional_module', type: 'object')
                        ]
                    )
                )
            ),
            new OA\Response(response: 400, description: 'Неверные параметры'),
            new OA\Response(response: 404, description: 'Лицензия или модуль не найден'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Валидация входных данных
        if (!isset($data['base_license_id'])) {
            return $this->json(['error' => 'Missing base_license_id parameter'], 400);
        }
        if (!isset($data['modules']) || !is_array($data['modules'])) {
            return $this->json(['error' => 'Missing or invalid modules parameter'], 400);
        }

        // Поиск базовой лицензии
        $baseLicense = $this->entityManager->getRepository(BaseLicense::class)->find($data['base_license_id']);
        if (!$baseLicense) {
            return $this->json(['error' => 'Base license not found'], 404);
        }

        // Удаляем старые связи для этой лицензии
        $oldCompositions = $this->entityManager->getRepository(LicenseComposition::class)
            ->findBy(['baseLicense' => $data['base_license_id']]);

        foreach ($oldCompositions as $oldComposition) {
            $this->entityManager->remove($oldComposition);
        }

        $results = [];
        foreach ($data['modules'] as $moduleData) {
            // Валидация данных модуля
            if (!isset($moduleData['module_id'])) {
                continue;
            }

            // Поиск модуля
            $additionalModule = $this->entityManager->getRepository(AdditionalModule::class)
                ->find($moduleData['module_id']);
            if (!$additionalModule) {
                continue;
            }

            // Создаем новую связь
            $licenseComposition = new LicenseComposition();
            $licenseComposition->setRequired($moduleData['required'] ?? false);
            $licenseComposition->setCompatible($moduleData['compatible'] ?? true);
            $licenseComposition->setBaseLicense($baseLicense);
            $licenseComposition->setAdditionalModule($additionalModule);

            $this->entityManager->persist($licenseComposition);
            $results[] = $licenseComposition;
        }

        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializer->serialize($results, 'json', [
                'groups' => ['composition:read', 'license:read', 'module:read']
            ]),
            200,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'license_composition_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/license-compositions/{id}',
        summary: 'Удалить связь',
        tags: ['License Composition'],
        responses: [
            new OA\Response(response: 204, description: 'Связь удалена'),
            new OA\Response(response: 404, description: 'Связь не найдена'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $licenseComposition = $this->licenseCompositionService->getLicenseComposition($id);

        if (!$licenseComposition) {
            return $this->json(['error' => 'License composition not found'], 404);
        }

        $this->licenseCompositionService->deleteLicenseComposition($licenseComposition);

        return $this->json([], 204);
    }

    #[Route('', name: 'license_composition_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/license-compositions',
        summary: 'Получить сгруппированный список связей базовых лицензий и дополнительных модулей',
        tags: ['License Composition'],
        parameters: [
            new OA\Parameter(
                name: 'required',
                in: 'query',
                required: false,
                description: 'Фильтр по обязательности (1 = входит, 0 = не входит)',
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'compatible',
                in: 'query',
                required: false,
                description: 'Фильтр по совместимости (1 = сочетается, 0 = не сочетается)',
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Поле сортировки (base_license_name или additional_module_name)',
                schema: new OA\Schema(type: 'string', enum: ['base_license_name', 'additional_module_name'])
            ),
            new OA\Parameter(
                name: 'direction',
                in: 'query',
                required: false,
                description: 'Направление сортировки (asc или desc)',
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])
            ),
            new OA\Parameter(
                name: 'base_license_name',
                in: 'query',
                required: false,
                description: 'Поиск по названию базовой лицензии (частичное совпадение)',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список связей',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'base_license_id', type: 'integer'),
                            new OA\Property(property: 'base_license_name', type: 'string'),
                            new OA\Property(
                                property: 'additional_modules',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer'),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'relation', type: 'string', description: 'входит / сочетается / не сочетается'),
                                    ],
                                    type: 'object'
                                )
                            )
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $required = $request->query->get('required');
        $compatible = $request->query->get('compatible');
        $sort = $request->query->get('sort', 'base_license_name');
        $direction = $request->query->get('direction', 'asc');
        $baseLicenseName = $request->query->get('base_license_name');

        $groupedCompositions = $this->licenseCompositionService->getAllGroupedCompositions([
            'required' => $required,
            'compatible' => $compatible,
            'sort' => $sort,
            'direction' => $direction,
            'base_license_name' => $baseLicenseName,
        ]);

        return $this->json($groupedCompositions);
    }

}