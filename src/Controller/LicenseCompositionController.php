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
        summary: 'Создать связь лицензии и модуля',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['required', 'compatible'],
                properties: [
                    new OA\Property(property: 'required', type: 'boolean'),
                    new OA\Property(property: 'compatible', type: 'boolean'),
                    new OA\Property(property: 'base_license_id', type: 'integer'),
                    new OA\Property(property: 'additional_module_id', type: 'integer')
                ]
            )
        ),
        tags: ['License Composition'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Связь создана',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'required', type: 'boolean'),
                        new OA\Property(property: 'compatible', type: 'boolean'),
                        new OA\Property(property: 'base_license', type: 'object'),
                        new OA\Property(property: 'additional_module', type: 'object')
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

        $required = $data['required'] ?? null;
        $compatible = $data['compatible'] ?? null;
        $baseLicenseId = $data['base_license_id'] ?? null;
        $additionalModuleId = $data['additional_module_id'] ?? null;

        if ($required === null || $compatible === null) {
            return $this->json(['error' => 'Missing required parameters'], 400);
        }

        $baseLicense = $baseLicenseId ? $this->entityManager->getRepository(BaseLicense::class)->find($baseLicenseId) : null;
        $additionalModule = $additionalModuleId ? $this->entityManager->getRepository(AdditionalModule::class)->find($additionalModuleId) : null;

        $licenseComposition = $this->licenseCompositionService->createLicenseComposition(
            $required,
            $compatible,
            $baseLicense,
            $additionalModule
        );

        return new JsonResponse(
            $this->serializer->serialize($licenseComposition, 'json', [
                'groups' => ['composition:read', 'license:read', 'module:read']
            ]),
            200,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'license_composition_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/license-compositions/{id}',
        summary: 'Получить связь по ID',
        tags: ['License Composition'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Связь найдена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'required', type: 'boolean'),
                        new OA\Property(property: 'compatible', type: 'boolean'),
                        new OA\Property(property: 'base_license', type: 'object'),
                        new OA\Property(property: 'additional_module', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Связь не найдена'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function get(int $id): JsonResponse
    {
        $licenseComposition = $this->licenseCompositionService->getLicenseComposition($id);

        if (!$licenseComposition) {
            return $this->json(['error' => 'License composition not found'], 404);
        }

        return new JsonResponse(
            $this->serializer->serialize($licenseComposition, 'json', [
                'groups' => ['composition:read', 'license:read', 'module:read']
            ]),
            200,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'license_composition_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/license-compositions/{id}',
        summary: 'Обновить связь',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'required', type: 'boolean'),
                    new OA\Property(property: 'compatible', type: 'boolean'),
                    new OA\Property(property: 'base_license_id', type: 'integer'),
                    new OA\Property(property: 'additional_module_id', type: 'integer')
                ]
            )
        ),
        tags: ['License Composition'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Связь обновлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'required', type: 'boolean'),
                        new OA\Property(property: 'compatible', type: 'boolean'),
                        new OA\Property(property: 'base_license', type: 'object'),
                        new OA\Property(property: 'additional_module', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Связь не найдена'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $licenseComposition = $this->licenseCompositionService->getLicenseComposition($id);

        if (!$licenseComposition) {
            return $this->json(['error' => 'License composition not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['required'])) {
            $licenseComposition->setRequired($data['required']);
        }

        if (isset($data['compatible'])) {
            $licenseComposition->setCompatible($data['compatible']);
        }

        if (isset($data['base_license_id'])) {
            $baseLicense = $this->entityManager->getRepository(BaseLicense::class)->find($data['base_license_id']);
            $licenseComposition->setBaseLicense($baseLicense);
        }

        if (isset($data['additional_module_id'])) {
            $additionalModule = $this->entityManager->getRepository(AdditionalModule::class)->find($data['additional_module_id']);
            $licenseComposition->setAdditionalModule($additionalModule);
        }

        $this->licenseCompositionService->updateLicenseComposition($licenseComposition);

        return new JsonResponse(
            $this->serializer->serialize($licenseComposition, 'json', [
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
        summary: 'Получить список всех связей',
        tags: ['License Composition'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список связей',
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
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function list(): JsonResponse
    {
        $licenseCompositions = $this->licenseCompositionService->getAllLicenseCompositions();

        return new JsonResponse(
            $this->serializer->serialize($licenseCompositions, 'json', [
                'groups' => ['composition:read', 'license:read', 'module:read']
            ]),
            200,
            [],
            true
        );
    }
}