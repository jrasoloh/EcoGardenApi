<?php

namespace App\Controller;

use App\Entity\Conseil;
use App\Manager\ConseilManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/conseil')]
class ConseilController extends AbstractController
{
    #[Route('/{mois}', name: 'app_conseil_mois', requirements: ['mois' => '\d+'], methods: ['GET'])]
    public function index(string $mois, ConseilManager $conseilManager, SerializerInterface $serializer): JsonResponse
    {
        $conseils = $conseilManager->getConseilsByMonth($mois);

        $jsonConseils = $serializer->serialize($conseils, 'json', ['groups' => 'conseil:read']);

        return new JsonResponse($jsonConseils, 200, [], true);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/', name: 'app_conseil_current', methods: ['GET'])]
    public function current(ConseilManager $conseilManager, SerializerInterface $serializer): JsonResponse
    {
        $conseils = $conseilManager->getCurrentMonthConseils();

        $jsonConseils = $serializer->serialize($conseils, 'json', ['groups' => 'conseil:read']);

        return new JsonResponse($jsonConseils, 200, [], true);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('', name: 'app_conseil_create', methods: ['POST'])]
    public function create(Request $request, ConseilManager $conseilManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['content']) || !isset($data['months'])) {
            return new JsonResponse(['error' => 'Champs manquants'], 400);
        }

        $newConseil = $conseilManager->createConseil($data['content'], $data['months']);

        $jsonConseil = $serializer->serialize($newConseil, 'json', ['groups' => 'conseil:read']);

        return new JsonResponse($jsonConseil, 201, [], true);
    }

    #[Route('/{id}', name: 'app_conseil_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Conseil $conseil, ConseilManager $conseilManager): JsonResponse
    {
        $conseilManager->deleteConseil($conseil);

        return new JsonResponse(null, 204);
    }
}
