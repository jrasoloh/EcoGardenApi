<?php

namespace App\Controller;

use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly SerializerInterface $serializer
    ){}

    /**
     * @throws ExceptionInterface
     */
    #[Route('/{id}', name: 'app_user_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux admins')]
    public function edit(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('id');
        if ($userId === null) {
            throw new NotFoundHttpException("L'utilisateur n'existe pas");
        }
        $userId = (int) $userId;

        $userData = json_decode($request->getContent(), true);
        if ($userData === null) {
            return new JsonResponse(['error' => "Format JSON invalide"], Response::HTTP_BAD_REQUEST);
        }

        $allowedFields = ['login', 'city', 'password', 'roles'];
        foreach ($userData as $key => $value) {
            if (!in_array($key, $allowedFields)) {
                return new JsonResponse([
                    'error' => "Le champ '$key' n'est pas autorisé ou n'existe pas."
                ], Response::HTTP_BAD_REQUEST);
            }
        }
        if (empty($userData)) {
            return new JsonResponse(['error' => "Aucune donnée envoyée"], Response::HTTP_BAD_REQUEST);
        }

        try {
            $userModel = $this->userManager->editUserById($userId, $userData);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $jsonUser = $this->serializer->serialize($userModel, 'json', ['groups' => 'user:read']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux admins')]
    public function delete(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('id');
        if ($userId === null) {
            throw new NotFoundHttpException("L'utilisateur n'existe pas");
        }
        $userId = (int) $userId;
        try {
            $this->userManager->removeUserById($userId);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
