<?php

namespace App\Controller;

use App\Dto\OfferFilterInput;
use App\Service\CommercialOfferSelector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OfferSelectionController extends AbstractController
{
    #[Route('/api/offers/select', name: 'select_offers', methods: ['POST'])]
    public function select(Request $request, CommercialOfferSelector $selector): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $input = new OfferFilterInput();
        $input->typeProduct = $data['typeProduct'] ?? null;
        $input->minPrice = $data['minPrice'] ?? 0;
        $input->maxPrice = $data['maxPrice'] ?? PHP_INT_MAX;
        $input->minQuantity = $data['minQuantity'] ?? 1;
        $input->discountPercent = $data['discountPercent'] ?? 0;

        $results = $selector->findSuitableOffers($input);

        return $this->json([
            'matched_offers' => $results,
        ]);
    }
}
