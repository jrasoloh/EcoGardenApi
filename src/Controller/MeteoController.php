<?php

namespace App\Controller;

use App\Service\MeteoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/meteo')]
class MeteoController extends AbstractController
{
    private MeteoService $meteoService;

    public function __construct(MeteoService $meteoService)
    {
        $this->meteoService = $meteoService;
    }

    #[Route('/{ville}', name: 'app_meteo_city', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(string $ville): JsonResponse
    {
        try {
            $meteo = $this->meteoService->getMeteoForCity($ville);
            return new JsonResponse($meteo);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Ville introuvable ou erreur API'], 404);
        }
    }

    #[Route('', name: 'app_meteo_user', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function weatherForUser(): JsonResponse
    {
        $user = $this->getUser();

        $ville = $user->getCity();

        if (!$ville) {
            return new JsonResponse(['error' => 'Aucune ville configurée pour cet utilisateur'], 404);
        }

        return $this->index($ville);
    }
}
