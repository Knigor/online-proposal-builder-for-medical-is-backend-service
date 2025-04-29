<?php

namespace App\Dto;

class OfferFilterInput
{
    public ?string $typeProduct = null;
    public ?int $minPrice = null;
    public ?int $maxPrice = null;
    public ?int $minQuantity = null;
    public ?int $discountPercent = null;
}
