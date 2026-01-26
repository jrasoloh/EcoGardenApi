<?php

namespace App\Manager;

use App\Entity\User;
use App\Model\UserModel;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager,
                                private readonly UserPasswordHasherInterface $passwordHasher,
                                private readonly UserRepository $userRepository
    ) {}

    public function registerUser(string $login, string $plainPassword, string $city): UserModel
    {
        $userEntity = $this->userRepository->createUser($login, $plainPassword, $city);

        return new UserModel(
            id: $userEntity->getId(),
            login: $userEntity->getLogin(),
            city: $userEntity->getCity(),
            roles: $userEntity->getRoles(),
        );
    }

    public function editUser(User $user, array $data): UserModel
    {
        if (array_key_exists('login', $data)) {
            $newLogin = $data['login'];

            if ($newLogin !== $user->getLogin()) {
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $newLogin]);

                if ($existingUser) {
                    throw new ConflictHttpException("Le login '$newLogin' est déjà utilisé par un autre utilisateur.");
                }

                $user->setLogin($newLogin);
            }
        }

        if (array_key_exists('city', $data)) {
            $user->setCity($data['city']);
        }

        if (array_key_exists('password', $data)) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        if (array_key_exists('roles', $data)) {
            if (!is_array($data['roles'])) {
                throw new BadRequestHttpException("Le format des rôles est invalide (tableau attendu).");
            }

            $allowedRoles = ['ROLE_USER', 'ROLE_ADMIN'];
            foreach ($data['roles'] as $role) {
                if (!in_array($role, $allowedRoles)) {
                    throw new BadRequestHttpException("Le rôle '$role' n'existe pas.");
                }
            }

            $user->setRoles($data['roles']);
        }

        $this->entityManager->flush();

        return new UserModel(
            id: $user->getId(),
            login: $user->getLogin(),
            city: $user->getCity(),
            roles: $user->getRoles()
        );
    }

    public function removeUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function editUserById(int $id, array $data): UserModel
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new NotFoundHttpException("L'utilisateur n'existe pas");
        }
        return $this->editUser($user, $data);
    }

    public function removeUserById(int $id): void
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new NotFoundHttpException("L'utilisateur n'existe pas");
        }
        $this->removeUser($user);
    }
}
