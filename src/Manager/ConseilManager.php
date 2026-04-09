<?php

namespace App\Manager;

use App\Entity\Conseil;
use App\Repository\ConseilRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConseilManager
{
    private ConseilRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(ConseilRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * Récupère les conseils pour un mois donné (format string '1', '2'...)
     */
    public function getConseilsByMonth(string $month): array
    {
        $allConseils = $this->repository->findAll();

        $filteredConseils = array_filter($allConseils, function($conseil) use ($month) {
            return in_array($month, $conseil->getMonths());
        });

        return array_values($filteredConseils);
    }

    /**
     * Récupère les conseils du mois actuel
     */
    public function getCurrentMonthConseils(): array
    {
        $currentMonth = date('n');
        return $this->getConseilsByMonth((string)$currentMonth);
    }

    /**
     * Crée un nouveau conseil à partir des données reçues
     */
    public function createConseil(string $content, array $months): Conseil
    {
        $conseil = new Conseil();
        $conseil->setContent($content);
        $conseil->setMonths($months);

        $this->entityManager->persist($conseil);
        $this->entityManager->flush();

        return $conseil;
    }

    /**
     * Met à jour partiellement un conseil
     */
    public function editConseil(Conseil $conseil, array $data): Conseil
    {
        if (array_key_exists('content', $data)) {
            $conseil->setContent($data['content']);
        }

        if (array_key_exists('months', $data)) {
            $conseil->setMonths($data['months']);
        }

        $this->entityManager->flush();

        return $conseil;
    }

    /**
     * Supprime un conseil de la base de données
     */
    public function deleteConseil(Conseil $conseil): void
    {
        $this->entityManager->remove($conseil);
        $this->entityManager->flush();
    }
}
