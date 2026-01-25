<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Conseil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setLogin('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCity('Paris');
        $password = $this->hasher->hashPassword($admin, 'test');
        $admin->setPassword($password);
        $manager->persist($admin);

        $user = new User();
        $user->setLogin('jardinier');
        $user->setCity('Lyon');
        $passwordUser = $this->hasher->hashPassword($user, 'test');
        $user->setPassword($passwordUser);
        $manager->persist($user);

        $conseil1 = new Conseil();
        $conseil1->setContent('En janvier, paillez les pieds des arbres.');
        $conseil1->setMonths(['1']);
        $manager->persist($conseil1);

        $conseil2 = new Conseil();
        $conseil2->setContent('Taillez les rosiers avant le printemps.');
        $conseil2->setMonths(['2', '3']);
        $manager->persist($conseil2);

        $manager->flush();
    }
}
