<?php

namespace App\Manager;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function createUser(string $login, string $plainPassword, string $city): User
    {
        $user = new User();
        $user->setLogin($login);
        $user->setRoles(['ROLE_USER']);

        $user->setCity($city);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function editUser(User $user, array $data): User
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

        $this->entityManager->flush();

        return $user;
    }

    public function removeUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
