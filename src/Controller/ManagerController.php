<?php

namespace App\Controller;

use App\Entity\ManagerLk;
use App\Entity\User;
use App\Entity\CommercialOffers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/manager-lk')]
class ManagerController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route('/', name: 'manager_lk_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->em->getRepository(User::class)->find($data['user_id']);
        $offer = $this->em->getRepository(CommercialOffers::class)->find($data['commercial_offer_id']);

        if (!$user || !$offer) {
            return $this->json(['error' => 'User or Offer not found'], 404);
        }

        $manager = new ManagerLk();
        $manager->setUserId($user)
            ->setCommercialOffersId($offer)
            ->setStatus($data['status'] ?? '')
            ->setEmailClient($data['email_client'] ?? '');

        $this->em->persist($manager);
        $this->em->flush();

        return $this->json(['message' => 'Created', 'id' => $manager->getId()]);
    }

    #[Route('', name: 'manager_lk_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $email = $request->query->get('email');

        $qb = $this->em->getRepository(ManagerLk::class)->createQueryBuilder('m');

        if ($status) {
            $qb->andWhere('m.status = :status')
                ->setParameter('status', $status);
        }

        if ($email) {
            $qb->andWhere('m.emailClient = :email')
                ->setParameter('email', $email);
        }

        $managers = $qb->getQuery()->getResult();

        $data = array_map(function (ManagerLk $manager) {
            return [
                'id' => $manager->getId(),
                'user_id' => $manager->getUserId()?->getId(),
                'commercial_offer_id' => $manager->getCommercialOffersId()?->getId(),
                'status' => $manager->getStatus(),
                'email_client' => $manager->getEmailClient()
            ];
        }, $managers);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'manager_lk_view', methods: ['GET'])]
    public function view(int $id): JsonResponse
    {
        $manager = $this->em->getRepository(ManagerLk::class)->find($id);

        if (!$manager) {
            return $this->json(['error' => 'Not found'], 404);
        }

        return $this->json([
            'id' => $manager->getId(),
            'user_id' => $manager->getUserId()?->getId(),
            'commercial_offer_id' => $manager->getCommercialOffersId()?->getId(),
            'status' => $manager->getStatus(),
            'email_client' => $manager->getEmailClient()
        ]);
    }

    #[Route('/{id}', name: 'manager_lk_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $manager = $this->em->getRepository(ManagerLk::class)->find($id);
        if (!$manager) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['user_id'])) {
            $user = $this->em->getRepository(User::class)->find($data['user_id']);
            if ($user) {
                $manager->setUserId($user);
            }
        }

        if (isset($data['commercial_offer_id'])) {
            $offer = $this->em->getRepository(CommercialOffers::class)->find($data['commercial_offer_id']);
            if ($offer) {
                $manager->setCommercialOffersId($offer);
            }
        }

        if (isset($data['status'])) {
            $manager->setStatus($data['status']);
        }

        if (isset($data['email_client'])) {
            $manager->setEmailClient($data['email_client']);
        }

        $this->em->flush();

        return $this->json(['message' => 'Updated']);
    }

    #[Route('/{id}', name: 'manager_lk_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $manager = $this->em->getRepository(ManagerLk::class)->find($id);

        if (!$manager) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $this->em->remove($manager);
        $this->em->flush();

        return $this->json(['message' => 'Deleted']);
    }
}
