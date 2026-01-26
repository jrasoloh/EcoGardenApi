<?php

namespace App\Controller;

use App\Entity\User;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('/{id}', name: 'app_user_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux admins')]
    public function edit(
        User $user,
        Request $request,
        UserManager $userManager,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return new JsonResponse(['error' => "Format JSON invalide"], 400);
        }

        $allowedFields = ['login', 'city', 'password', 'roles'];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedFields)) {
                return new JsonResponse([
                    'error' => "Le champ '$key' n'est pas autorisé ou n'existe pas."
                ], 400);
            }
        }

        if (empty($data)) {
            return new JsonResponse(['error' => "Aucune donnée envoyée"], 400);
        }

        $userManager->editUser($user, $data);

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($jsonUser, 200, [], true);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux admins')]
    public function delete(User $user, UserManager $userManager): JsonResponse
    {
        $userManager->removeUser($user);

        return new JsonResponse(null, 204);
    }
}
