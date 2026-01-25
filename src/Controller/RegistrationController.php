<?php

namespace App\Controller;

use App\Manager\UserManager;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/register')]
class RegistrationController extends AbstractController
{
    #[Route('', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserManager $userManager,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['login']) || empty($data['password']) || empty($data['city'])) {
            return new JsonResponse(['error' => 'Login, password et city sont obligatoires'], 400);
        }

        try {
            $user = $userManager->createUser(
                $data['login'],
                $data['password'],
                $data['city']
            );
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'Ce login est déjà utilisé'], 409);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'inscription'], 500);
        }

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($jsonUser, 201, [], true);
    }
}
