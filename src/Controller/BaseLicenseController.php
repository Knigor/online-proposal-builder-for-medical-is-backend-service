<?php

namespace App\Controller;

use App\Entity\BaseLicense;
use App\Repository\BaseLicenseRepository;
use App\Service\BaseLicenseService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[Route('/api/base-licenses')]
#[Security(name: 'Bearer')]
class BaseLicenseController extends AbstractController
{
    public function __construct(
        private BaseLicenseService $baseLicenseService,
        private EntityManagerInterface $entityManager,
        private BaseLicenseRepository $baseLicenseRepository
    ) {}

    #[Route('', name: 'create_base_license', methods: ['POST'])]
    #[OA\Post(
        path: '/api/base-licenses',
        tags: ['BaseLicenses'],
        summary: 'Создание базовой лицензии',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name_license", "description_license", "offer_price_license", "purchase_price_license", "max_discount", "type_license"],
                properties: [
                    new OA\Property(property: "name_license", type: "string"),
                    new OA\Property(property: "description_license", type: "string"),
                    new OA\Property(property: "offer_price_license", type: "number", format: "float"),
                    new OA\Property(property: "purchase_price_license", type: "number", format: "float"),
                    new OA\Property(property: "max_discount", type: "number", format: "float"),
                    new OA\Property(property: "type_license", type: "string"),
                    new OA\Property(property: "product_id", type: "integer", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Лицензия создана'),
        ]
    )]
    #[Groups(['license:read'])]
    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $license = $this->baseLicenseService->createBaseLicense($data);

        return $this->json($license, 201, [], ['groups' => 'license:read']);
    }

    #[Route('', name: 'list_base_licenses', methods: ['GET'])]
    #[OA\Get(
        path: '/api/base-licenses',
        tags: ['BaseLicenses'],
        summary: 'Получить список всех базовых лицензий',
        responses: [new OA\Response(response: 200, description: 'Список лицензий')]
    )]
    #[Groups(['license:read'])]
    public function list(): JsonResponse
    {
        $licenses = $this->baseLicenseRepository->findAll();
        return $this->json($licenses, 200, [], ['groups' => ['license:read']]);
    }

    #[Route('/{id}', name: 'get_base_license', methods: ['GET'])]
    #[OA\Get(
        path: '/api/base-licenses/{id}',
        tags: ['BaseLicenses'],
        summary: 'Получить базовую лицензию по ID',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Данные лицензии'),
            new OA\Response(response: 404, description: 'Лицензия не найдена')
        ]
    )]
    #[Groups(['license:read'])]
    public function get(int $id): JsonResponse
    {
        $license = $this->baseLicenseRepository->find($id);
        if (!$license) {
            return $this->json(['message' => 'License not found'], 404);
        }
        return $this->json($license, 200, [], ['groups' => ['license:read']]);
    }

    #[Route('/{id}', name: 'update_base_license', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/base-licenses/{id}',
        tags: ['BaseLicenses'],
        summary: 'Обновить базовую лицензию',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name_license", type: "string"),
                    new OA\Property(property: "description_license", type: "string"),
                    new OA\Property(property: "offer_price_license", type: "number", format: "float"),
                    new OA\Property(property: "purchase_price_license", type: "number", format: "float"),
                    new OA\Property(property: "max_discount", type: "number", format: "float"),
                    new OA\Property(property: "type_license", type: "string"),
                    new OA\Property(property: "product_id", type: "integer", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Лицензия обновлена'),
            new OA\Response(response: 404, description: 'Лицензия не найдена')
        ]
    )]
    #[Groups(['license:read'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $license = $this->baseLicenseRepository->find($id);
        if (!$license) {
            return $this->json(['message' => 'License not found'], 404);
        }

        $data = $request->toArray();
        $updatedLicense = $this->baseLicenseService->updateBaseLicense($license, $data);

        return $this->json($updatedLicense, 200, [], ['groups' => ['license:read']]);
    }

    #[Route('/{id}', name: 'delete_base_license', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/base-licenses/{id}',
        tags: ['BaseLicenses'],
        summary: 'Удалить базовую лицензию',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Лицензия удалена'),
            new OA\Response(response: 404, description: 'Лицензия не найдена')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $license = $this->baseLicenseRepository->find($id);
        if (!$license) {
            return $this->json(['message' => 'License not found'], 404);
        }

        $this->entityManager->remove($license);
        $this->entityManager->flush();

        return $this->json(['message' => 'License deleted']);
    }
}
