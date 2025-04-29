<?php

namespace App\Repository;

use App\Entity\ManagerLk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ManagerLk>
 */
class ManagerLkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ManagerLk::class);
    }

    /**
     * @throws Exception
     */
    public function getCommercialOfferDetails(int $commercialId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
        SELECT 
            co.created_at, 
            co.status, 
            co.total_price, 
            p.name_product,
            p.discription_product,
            p.type_product,
            pl.price,
            pl.discount_percent,
            pl.quantity,
            ml.email_client,
            ml.status as manager_status,
            u.full_name,
            u.email 
        FROM commercial_offers co
        LEFT JOIN commercial_offers_items coi ON coi.commercial_offer_id_id = co.id
        LEFT JOIN product p ON p.id = coi.product_id_id
        LEFT JOIN price_list pl ON pl.product_id = p.id
        LEFT JOIN manager_lk ml ON ml.commercial_offers_id_id = co.id 
        LEFT JOIN "user" u ON u.id = ml.user_id_id 
        WHERE co.id = :id
    SQL;

        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery(['id' => $commercialId])->fetchAllAssociative();
    }

}
