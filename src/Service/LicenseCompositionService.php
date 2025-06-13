<?php

namespace App\Service;

use App\Entity\LicenseComposition;
use App\Entity\BaseLicense;
use App\Entity\AdditionalModule;
use App\Repository\LicenseCompositionRepository;
use Doctrine\ORM\EntityManagerInterface;

class LicenseCompositionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenseCompositionRepository $licenseCompositionRepository
    ) {
    }

    public function createLicenseComposition(
        bool $required,
        bool $compatible,
        ?BaseLicense $baseLicense,
        ?AdditionalModule $additionalModule
    ): LicenseComposition {
        $licenseComposition = new LicenseComposition();
        $licenseComposition->setRequired($required);
        $licenseComposition->setCompatible($compatible);
        $licenseComposition->setBaseLicense($baseLicense);
        $licenseComposition->setAdditionalModule($additionalModule);

        $this->entityManager->persist($licenseComposition);
        $this->entityManager->flush();

        return $licenseComposition;
    }

    public function getLicenseComposition(int $id): ?LicenseComposition
    {
        return $this->licenseCompositionRepository->find($id);
    }

    public function updateLicenseComposition(LicenseComposition $licenseComposition): void
    {
        $this->entityManager->flush();
    }

    public function deleteLicenseComposition(LicenseComposition $licenseComposition): void
    {
        $this->entityManager->remove($licenseComposition);
        $this->entityManager->flush();
    }

    public function getAllLicenseCompositions(): array
    {
        return $this->licenseCompositionRepository->findAll();
    }

    public function getAllGroupedCompositions(array $filters = []): array
    {
        return $this->licenseCompositionRepository->findGroupedByBaseLicense($filters);
    }
}