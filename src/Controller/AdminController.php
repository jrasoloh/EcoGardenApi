<?php

namespace App\Controller;

use App\Service\MeteoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/cache/meteo/{city}', name: 'app_admin_cache_meteo_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs')]
    public function clearMeteoCache(string $city, MeteoService $meteoService): JsonResponse
    {
        $meteoService->clearCache($city);

        return new JsonResponse([
            'message' => "Le cache météo pour la ville '$city' a été vidé avec succès."
        ], 200);
    }
}
