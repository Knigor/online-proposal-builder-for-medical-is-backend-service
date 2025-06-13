<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/customers')]
#[OA\Tag(name: 'Customers')]
class CustomerController extends AbstractController
{
    /**
     * Получить список всех заказчиков
     */
    #[Route('', name: 'customer_index', methods: ['GET'])]
    #[OA\Get(
        description: 'Получение списка всех заказчиков',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список заказчиков',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Customer::class))
                ))
        ]
    )]
    public function index(CustomerRepository $customerRepository): JsonResponse
    {
        $customers = $customerRepository->findAll();
        return $this->json($customers);
    }

    /**
     * Создать нового заказчика
     */
    #[Route('', name: 'customer_create', methods: ['POST'])]
    #[OA\Post(
        description: 'Создание нового заказчика',
        requestBody: new OA\RequestBody(
            description: 'Данные заказчика',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Customer::class, groups: ['create']))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Заказчик успешно создан',
                content: new OA\JsonContent(ref: new Model(type: Customer::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Некорректные данные'
            )
        ]
    )]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

            $errors = $validator->validate($customer);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $em->persist($customer);
            $em->flush();

            return $this->json($customer, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ошибка при создании заказчика',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Получить информацию о заказчике
     */
    #[Route('/{id}', name: 'customer_show', methods: ['GET'])]
    #[OA\Get(
        description: 'Получение информации о конкретном заказчике',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID заказчика',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о заказчике',
                content: new OA\JsonContent(ref: new Model(type: Customer::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Заказчик не найден'
            )
        ]
    )]
    public function show(Customer $customer): JsonResponse
    {
        return $this->json($customer);
    }

    /**
     * Обновить данные заказчика
     */
    #[Route('/{id}', name: 'customer_update', methods: ['PUT', 'PATCH'])]
    #[OA\Put(
        description: 'Полное обновление данных заказчика',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID заказчика',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            description: 'Обновленные данные заказчика',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Customer::class, groups: ['update']))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные заказчика обновлены',
                content: new OA\JsonContent(ref: new Model(type: Customer::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Некорректные данные'
            ),
            new OA\Response(
                response: 404,
                description: 'Заказчик не найден'
            )
        ]
    )]
    #[OA\Patch(
        description: 'Частичное обновление данных заказчика',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID заказчика',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            description: 'Частичные данные заказчика',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Customer::class, groups: ['update']))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные заказчика обновлены',
                content: new OA\JsonContent(ref: new Model(type: Customer::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Некорректные данные'
            ),
            new OA\Response(
                response: 404,
                description: 'Заказчик не найден'
            )
        ]
    )]
    public function update(
        Customer $customer,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $serializer->deserialize(
                $request->getContent(),
                Customer::class,
                'json',
                ['object_to_populate' => $customer]
            );

            $errors = $validator->validate($customer);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $em->flush();

            return $this->json($customer);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ошибка при обновлении заказчика',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Удалить заказчика
     */
    #[Route('/{id}', name: 'customer_delete', methods: ['DELETE'])]
    #[OA\Delete(
        description: 'Удаление заказчика',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID заказчика',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Заказчик удален'
            ),
            new OA\Response(
                response: 404,
                description: 'Заказчик не найден'
            ),
            new OA\Response(
                response: 400,
                description: 'Невозможно удалить заказчика (есть связанные коммерческие предложения)'
            )
        ]
    )]
    public function delete(Customer $customer, EntityManagerInterface $em): JsonResponse
    {
        if ($customer->getCommercialOffers()->count() > 0) {
            return $this->json([
                'error' => 'Невозможно удалить заказчика, так как существуют связанные коммерческие предложения'
            ], Response::HTTP_BAD_REQUEST);
        }

        $em->remove($customer);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}