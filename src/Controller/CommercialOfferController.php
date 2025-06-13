<?php

namespace App\Controller;

use App\Entity\AdditionalModule;
use App\Entity\BaseLicense;
use App\Entity\CommercialOffers;
use App\Entity\Product;
use App\Service\CommercialOfferService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/commercial-offers')]
class CommercialOfferController extends AbstractController
{
    public function __construct(
        private CommercialOfferService $commercialOfferService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'commercial_offer_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/commercial-offers',
        summary: 'Создать новое коммерческое предложение',
        tags: ['Commercial Offer'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'КП создано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function create(): JsonResponse
    {
        $user = $this->getUser();
        $offer = $this->commercialOfferService->createCommercialOffer($user);

        return $this->json([
            'id' => $offer->getId(),
            'created_at' => $offer->getCreatedAt()->format('Y-m-d H:i:s')
        ], 201);
    }

    #[Route('/{id}', name: 'commercial_offer_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/commercial-offers/{id}',
        summary: 'Удалить коммерческое предложение',
        tags: ['Commercial Offer'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Коммерческое предложение удалено'),
            new OA\Response(response: 404, description: 'КП не найдено'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function delete(int $id): JsonResponse
    {

        $this->commercialOfferService->deleteCommercialOffer($id);
        return new JsonResponse(null, 204);
    }

    #[Route('/{id}/items', name: 'commercial_offer_add_item', methods: ['POST'])]
    #[OA\Post(
        path: '/api/commercial-offers/{id}/items',
        summary: 'Добавить продукт в КП',
        tags: ['Commercial Offer'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer'),
                    new OA\Property(property: 'base_license_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'additional_module_id', type: 'integer', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Продукт добавлен в КП',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(
                            property: 'product',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string')
                            ]
                        ),
                        new OA\Property(
                            property: 'base_license',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string')
                            ]
                        ),
                        new OA\Property(
                            property: 'additional_module',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string')
                            ]
                        ),
                        new OA\Property(property: 'quantity', type: 'integer'),
                        new OA\Property(property: 'price', type: 'number', format: 'float'),
                        new OA\Property(property: 'discount', type: 'number', format: 'float')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'КП или продукт не найден'),
            new OA\Response(response: 400, description: 'Некорректные параметры'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function addItem(CommercialOffers $offer, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $product = $this->entityManager->getRepository(Product::class)->find($data['product_id'] ?? 0);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $baseLicense = isset($data['base_license_id']) ?
            $this->entityManager->getRepository(BaseLicense::class)->find($data['base_license_id']) :
            null;

        $additionalModule = isset($data['additional_module_id']) ?
            $this->entityManager->getRepository(AdditionalModule::class)->find($data['additional_module_id']) :
            null;

        $quantity = 1; // всегда 1, поле не принимается от клиента

        $item = $this->commercialOfferService->addProductToOffer(
            $offer,
            $product,
            $baseLicense,
            $additionalModule,
            $quantity
        );

        return $this->json($item, 200, [], ['groups' => ['offer:item:read']]);
    }



    #[Route('', name: 'commercial_offer_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/commercial-offers',
        summary: 'Получить список всех коммерческих предложений',
        tags: ['Commercial Offer'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список коммерческих предложений',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'status', type: 'boolean'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'total_price', type: 'integer'),
                            new OA\Property(property: 'accepted_at', type: 'string', format: 'date-time', nullable: true),
                            new OA\Property(
                                property: 'items_count',
                                type: 'integer',
                                description: 'Количество позиций в КП'
                            )
                        ]
                    )
                )
            ),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function list(): JsonResponse
    {
        $user = $this->getUser();

        $offers = $this->entityManager->createQueryBuilder()
            ->select('co')
            ->from(CommercialOffers::class, 'co')
            ->innerJoin('co.userId', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->orderBy('co.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($offers as $offer) {
            $result[] = [
                'id' => $offer->getId(),
                'status' => $offer->isStatus(),
                'created_at' => $offer->getCreatedAt()->format('Y-m-d H:i:s'),
                'total_price' => $offer->getTotalPrice(),
                'accepted_at' => $offer->getAcceptedAt()?->format('Y-m-d H:i:s'),
                'items_count' => $offer->getCommercialOffersItems()->count(),
            ];
        }

        return $this->json($result);
    }

    #[Route('/{id}', name: 'commercial_offer_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/commercial-offers/{id}',
        summary: 'Получить детали коммерческого предложения',
        tags: ['Commercial Offer'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Детали коммерческого предложения',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'status', type: 'boolean'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'total_price', type: 'integer'),
                        new OA\Property(property: 'accepted_at', type: 'string', format: 'date-time', nullable: true),
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(
                                        property: 'product',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'name', type: 'string')
                                        ]
                                    ),
                                    new OA\Property(
                                        property: 'base_license',
                                        type: 'object',
                                        nullable: true,
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'name', type: 'string'),
                                            new OA\Property(property: 'price', type: 'number', format: 'float')
                                        ]
                                    ),
                                    new OA\Property(
                                        property: 'additional_modules',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'integer'),
                                                new OA\Property(property: 'name', type: 'string'),
                                                new OA\Property(property: 'price', type: 'number', format: 'float'),
                                                new OA\Property(property: 'required', type: 'boolean'),
                                                new OA\Property(property: 'compatible', type: 'boolean')
                                            ]
                                        )
                                    ),
                                    new OA\Property(property: 'quantity', type: 'integer'),
                                    new OA\Property(property: 'price', type: 'number', format: 'float'),
                                    new OA\Property(property: 'discount', type: 'number', format: 'float', nullable: true),
                                    new OA\Property(property: 'total', type: 'number', format: 'float')
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'КП не найдено'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function show(CommercialOffers $offer): JsonResponse
    {
        $items = [];
        foreach ($offer->getCommercialOffersItems() as $item) {
            $modules = [];

            // Получаем все связанные модули для этой лицензии
            if ($item->getBaseLicense()) {
                foreach ($item->getBaseLicense()->getLicenseCompositions() as $composition) {
                    if ($composition->getAdditionalModule()) {
                        $modules[] = [
                            'id' => $composition->getAdditionalModule()->getId(),
                            'name' => $composition->getAdditionalModule()->getNameModule(),
                            'price' => $composition->getAdditionalModule()->getPurchasePrice(),
                            'required' => $composition->isRequired(),
                            'compatible' => $composition->isCompatible()
                        ];
                    }
                }
            }

            $items[] = [
                'id' => $item->getId(),
                'product' => [
                    'id' => $item->getProduct()->getId(),
                    'name' => $item->getProduct()->getNameProduct(),
                ],
                'base_license' => $item->getBaseLicense() ? [
                    'id' => $item->getBaseLicense()->getId(),
                    'name' => $item->getBaseLicense()->getNameLicense(),
                    'price' => $item->getBaseLicense()->getPurchasePriceLicense(),
                ] : null,
                'additional_modules' => $modules,
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
                'discount' => $item->getDiscount(),
                'total' => $item->getPrice() * $item->getQuantity()
            ];
        }

        return $this->json([
            'id' => $offer->getId(),
            'status' => $offer->isStatus(),
            'created_at' => $offer->getCreatedAt()->format('Y-m-d H:i:s'),
            'total_price' => $offer->getTotalPrice(),
            'accepted_at' => $offer->getAcceptedAt()?->format('Y-m-d H:i:s'),
            'items' => $items
        ]);
    }

    #[Route('/{id}/compatible-modules/{baseLicenseId}', name: 'commercial_offer_compatible_modules', methods: ['GET'])]
    #[OA\Get(
        path: '/api/commercial-offers/{id}/compatible-modules/{baseLicenseId}',
        summary: 'Получить совместимые модули для лицензии',
        tags: ['Commercial Offer'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'baseLicenseId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список совместимых модулей',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'price', type: 'number', format: 'float')
                        ]
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Лицензия не найдена'),
            new OA\Response(response: 401, description: 'JWT Token not found or invalid')
        ]
    )]
    public function getCompatibleModules(CommercialOffers $offer, int $baseLicenseId): JsonResponse
    {
        $baseLicense = $this->entityManager->getRepository(BaseLicense::class)->find($baseLicenseId);
        if (!$baseLicense) {
            return $this->json(['error' => 'Base license not found'], 404);
        }

        $modules = $this->commercialOfferService->getCompatibleModules($baseLicense);

        $result = [];
        foreach ($modules as $module) {
            $result[] = [
                'id' => $module->getId(),
                'name' => $module->getName(),
                'price' => $module->getPrice()
            ];
        }

        return $this->json($result);
    }
}
