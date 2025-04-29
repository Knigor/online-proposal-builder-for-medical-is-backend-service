<?php

namespace App\Service;

use App\Dto\OfferFilterInput;
use App\Repository\CommercialOffersItemsRepository;

class CommercialOfferSelector
{
    public function __construct(
        private readonly CommercialOffersItemsRepository $itemsRepository
    ) {}

    public function findSuitableOffers(OfferFilterInput $input): array
    {
        $qb = $this->itemsRepository->createQueryBuilder('i')
            ->join('i.productId', 'p')
            ->join('p.priceLists', 'pl')
            ->join('i.commercialOfferId', 'co')
            ->where('p.typeProduct LIKE :typeProduct')
            ->andWhere('pl.price BETWEEN :minPrice AND :maxPrice')
            ->andWhere('pl.quantity >= :minQuantity')
            ->setParameter('typeProduct', '%' . $input->typeProduct . '%')
            ->setParameter('minPrice', $input->minPrice)
            ->setParameter('maxPrice', $input->maxPrice)
            ->setParameter('minQuantity', $input->minQuantity);


        if ($input->discountPercent > 0) {
            $qb->andWhere('pl.discountPercent > :discountPercent')
                ->setParameter('discountPercent', 0);
        }

        $qb->select('DISTINCT co.id AS commercial_offer_id');

        return $qb->getQuery()->getArrayResult();
    }
}
