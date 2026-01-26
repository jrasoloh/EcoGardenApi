<?php

namespace App\Controller;

use App\Manager\UserManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/register')]
class RegistrationController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserManager $userManager,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['login']) || empty($data['password']) || empty($data['city'])) {
            return new JsonResponse(['error' => 'Login, password et city sont obligatoires'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $userManager->registerUser(
                $data['login'],
                $data['password'],
                $data['city']
            );
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'Ce login est déjà utilisé'], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'inscription'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => ['user:write', 'user:read']]);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }
}
