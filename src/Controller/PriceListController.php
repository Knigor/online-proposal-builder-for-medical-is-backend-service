<?php

// src/Controller/PriceListController.php
namespace App\Controller;

use App\Entity\PriceList;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PriceListController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // Добавление нового ценового листа
// Добавление нового ценового листа
    #[Route('/api/price-list/add', name: 'add_price_list', methods: ['POST'])]
    public function addPriceList(Request $request): JsonResponse
    {
        // Получаем данные из тела запроса в формате JSON
        $data = json_decode($request->getContent(), true);

        // Проверяем, что данные корректно переданы
        if (!$data) {
            return $this->json(['message' => 'Invalid JSON data'], 400);
        }

        // Получаем параметры из данных
        $productId = (int) $data['product_id'];
        $price = $data['price'];
        $discountPercent = $data['discount_percent'];
        $quantity = $data['quantity'];


        // Находим продукт по ID
        $product = $this->em->getRepository(Product::class)->find($productId);
        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $priceList = new PriceList();
        $priceList->setProduct($product);
        $priceList->setPrice($price);
        $priceList->setDiscountPercent($discountPercent);
        $priceList->setQuantity($quantity);

        $this->em->persist($priceList);
        $this->em->flush();

        return $this->json(['message' => 'Price list added successfully']);
    }

    // Получение всех ценовых листов
    #[Route('/api/price-list', name: 'get_price_lists', methods: ['GET'])]
    public function getPriceLists(): JsonResponse
    {
        $priceLists = $this->em->getRepository(PriceList::class)->findAll();

        if (!$priceLists) {
            return $this->json(['message' => 'No price lists found'], 404);
        }

        $data = [];
        foreach ($priceLists as $priceList) {
            $data[] = [
                'id' => $priceList->getId(),
                'product' => $priceList->getProduct()->getNameProduct(),
                'price' => $priceList->getPrice(),
                'discount_percent' => $priceList->getDiscountPercent(),
                'quantity' => $priceList->getQuantity(),
            ];
        }

        return $this->json($data);
    }

    // Обновление ценового листа
// Обновление ценового листа
    #[Route('/api/price-list/update/{id}', name: 'update_price_list', methods: ['PUT'])]
    public function updatePriceList(int $id, Request $request): JsonResponse
    {
        // Получаем данные из тела запроса в формате JSON
        $data = json_decode($request->getContent(), true);

        // Проверяем, что данные корректно переданы
        if (!$data) {
            return $this->json(['message' => 'Invalid JSON data'], 400);
        }

        // Находим ценовой лист по ID
        $priceList = $this->em->getRepository(PriceList::class)->find($id);
        if (!$priceList) {
            return $this->json(['message' => 'Price list not found'], 404);
        }

        // Обновляем только те поля, которые были переданы
        if (isset($data['price'])) {
            $priceList->setPrice($data['price']);
        }

        if (isset($data['discount_percent'])) {
            $priceList->setDiscountPercent($data['discount_percent']);
        }

        if (isset($data['quantity'])) {
            $priceList->setQuantity($data['quantity']);
        }

        // Сохраняем изменения
        $this->em->flush();

        return $this->json(['message' => 'Price list updated successfully']);
    }


    // Удаление ценового листа
    #[Route('/api/price-list/delete/{id}', name: 'delete_price_list', methods: ['DELETE'])]
    public function deletePriceList(int $id): JsonResponse
    {
        $priceList = $this->em->getRepository(PriceList::class)->find($id);
        if (!$priceList) {
            return $this->json(['message' => 'Price list not found'], 404);
        }

        $this->em->remove($priceList);
        $this->em->flush();

        return $this->json(['message' => 'Price list deleted successfully']);
    }
}
