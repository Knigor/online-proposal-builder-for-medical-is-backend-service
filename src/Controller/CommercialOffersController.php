<?php

// src/Controller/CommercialOffersController.php
namespace App\Controller;

use App\Entity\CommercialOffers;
use App\Entity\CommercialOffersItems;
use App\Entity\PriceList;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\ManagerLk;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CommercialOffersController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // Добавление коммерческого предложения
    #[Route('/api/commercial-offer/add', name: 'add_commercial_offer', methods: ['POST'])]
    public function addCommercialOffer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Invalid JSON data'], 400);
        }

        // Проверка данных
        if (empty($data['user_id']) || empty($data['products'])) {
            return $this->json(['message' => 'Missing required fields'], 400);
        }

        // Создание коммерческого предложения
        $commercialOffer = new CommercialOffers();
        $commercialOffer->setStatus($data['status'] ?? false);
        $commercialOffer->setCreatedAt(new \DateTimeImmutable());


        // Добавляем пользователя
        $user = $this->em->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }
        $commercialOffer->addUserId($user);

        // Добавление товаров в предложение
        $totalPrice = 0;
        foreach ($data['products'] as $productData) {
            $product = $this->em->getRepository(Product::class)->find($productData['product_id']);
            if (!$product) {
                return $this->json(['message' => 'Product not found'], 404);
            }

            $priceList = $this->em->getRepository(PriceList::class)->findOneBy(['product' => $product]);
            if (!$priceList) {
                return $this->json(['message' => 'Price not found for the product'], 404);
            }

            $quantity = $productData['quantity'] ?? 1;

            $price = $priceList->getPrice();
            $discount = $priceList->getDiscountPercent(); // например, 10 = 10%
            $priceAfterDiscount = $price * (1 - $discount / 100);
            $itemTotal = $priceAfterDiscount * $quantity;

            $totalPrice += $itemTotal;

            $commercialOfferItem = new CommercialOffersItems();
            $commercialOfferItem->setProductId($product);
            $commercialOffer->addCommercialOffersItem($commercialOfferItem);
        }

        // Обновляем общую цену предложения
        $commercialOffer->setTotalPrice($totalPrice);

        // Сохраняем предложение в базу
        $this->em->persist($commercialOffer);
        $this->em->flush();

        return $this->json(['message' => 'Commercial offer added successfully']);
    }

    // Получение всех коммерческих предложений
    #[Route('/api/commercial-offers', name: 'get_commercial_offers', methods: ['GET'])]
    public function getCommercialOffers(): JsonResponse
    {
        $commercialOffers = $this->em->getRepository(CommercialOffers::class)->findAll();

        if (!$commercialOffers) {
            return $this->json(['message' => 'No commercial offers found'], 404);
        }

        $data = [];

        foreach ($commercialOffers as $offer) {
            $items = [];

            foreach ($offer->getCommercialOffersItems() as $item) {
                $product = $item->getProductId();

                // Находим PriceList для продукта
                $priceList = $this->em->getRepository(\App\Entity\PriceList::class)->findOneBy(['product' => $product]);

                $items[] = [
                    'id' => $product->getId(),
                    'name' => $product->getNameProduct(),
                    'type' => $product->getTypeProduct(),
                    'price' => $priceList?->getPrice(),
                    'discount_percent' => $priceList?->getDiscountPercent(),
                ];
            }


            $managerLk = $this->em->getRepository(ManagerLk::class)->findOneBy(['commercialOffersId' => $offer->getId()]);
            $statusManager = $managerLk ? $managerLk->getStatus() : null;

            $data[] = [
                'id' => $offer->getId(),
                'status' => $offer->isStatus(),
                'status_manager' => $statusManager,
                'created_at' => $offer->getCreatedAt()->format('Y-m-d H:i:s'),
                'total_price' => $offer->getTotalPrice(),
                'products' => $items,
            ];
        }

        return $this->json($data);
    }



    // получаем статистику

    /**
     * @throws \DateMalformedStringException
     */
    #[Route('/api/sales-report', name: 'sales_report', methods: ['GET'])]
    public function getSalesReport(Request $request): JsonResponse
    {
        $startDateParam = $request->query->get('start_date');
        $endDateParam = $request->query->get('end_date');

        $startDate = $startDateParam ? new \DateTime($startDateParam . ' 00:00:00') : null;
        $endDate = $endDateParam ? new \DateTime($endDateParam . ' 23:59:59') : null;

        $commercialOffers = $this->em->getRepository(CommercialOffers::class)->findAll();

        if (!$commercialOffers) {
            return $this->json(['message' => 'No sales data found'], 404);
        }

        $totalRevenue = 0;
        $totalProductsSold = 0;
        $productsData = [];

        foreach ($commercialOffers as $offer) {
            // Фильтрация по статусу (если нужно) и по датам
            if (!$offer->isStatus()) {
                continue;
            }

            $offerDate = $offer->getCreatedAt();

            if ($startDate && $offerDate < $startDate) {
                continue;
            }

            if ($endDate && $offerDate > $endDate) {
                continue;
            }

            foreach ($offer->getCommercialOffersItems() as $item) {
                $product = $item->getProductId();

                if (!$product) {
                    continue;
                }

                $priceList = $this->em->getRepository(\App\Entity\PriceList::class)->findOneBy(['product' => $product]);
                $price = $priceList ? $priceList->getPrice() : 0;

                $productId = $product->getId();
                $productName = $product->getNameProduct();
                $productType = $product->getTypeProduct();

                if (!isset($productsData[$productId])) {
                    $productsData[$productId] = [
                        'id' => $productId,
                        'name' => $productName,
                        'type' => $productType,
                        'quantity' => 0,
                        'total_sales' => 0,
                    ];
                }

                $productsData[$productId]['quantity'] += 1;
                $productsData[$productId]['total_sales'] += $price;

                $totalProductsSold++;
                $totalRevenue += $price;
            }
        }

        return $this->json([
            'total_products_sold' => $totalProductsSold,
            'total_revenue' => $totalRevenue,
            'products' => array_values($productsData),
        ]);
    }

    // Обновление коммерческого предложения
    #[Route('/api/commercial-offer/update/{id}', name: 'update_commercial_offer', methods: ['PUT'])]
    public function updateCommercialOffer(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $commercialOffer = $this->em->getRepository(CommercialOffers::class)->find($id);
        if (!$commercialOffer) {
            return $this->json(['message' => 'Commercial offer not found'], 404);
        }

        // Обновление статуса или других полей
        if (isset($data['status'])) {
            $commercialOffer->setStatus($data['status']);
        }
        if (isset($data['total_price'])) {
            $commercialOffer->setTotalPrice($data['total_price']);
        }

        // Обновление товаров
        if (isset($data['products'])) {
            foreach ($data['products'] as $productData) {
                $product = $this->em->getRepository(Product::class)->find($productData['product_id']);
                if ($product) {
                    $commercialOfferItem = new CommercialOffersItems();
                    $commercialOfferItem->setProductId($product);
                    $commercialOfferItem->setQuantity($productData['quantity']);
                    $commercialOfferItem->setUnitPrice($productData['unit_price']);
                    $commercialOfferItem->setDiscountPercent($productData['discount_percent']);
                    $commercialOffer->addCommercialOffersItem($commercialOfferItem);
                }
            }
        }

        $this->em->flush();

        return $this->json(['message' => 'Commercial offer updated successfully']);
    }

    // Удаление коммерческого предложения
    #[Route('/api/commercial-offer/delete/{id}', name: 'delete_commercial_offer', methods: ['DELETE'])]
    public function deleteCommercialOffer(int $id): JsonResponse
    {
        $commercialOffer = $this->em->getRepository(CommercialOffers::class)->find($id);
        if (!$commercialOffer) {
            return $this->json(['message' => 'Commercial offer not found'], 404);
        }

        $this->em->remove($commercialOffer);
        $this->em->flush();

        return $this->json(['message' => 'Commercial offer deleted successfully']);
    }
}
