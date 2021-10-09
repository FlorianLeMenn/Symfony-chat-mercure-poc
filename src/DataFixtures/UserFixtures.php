<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
    // create 20 users! Bam!
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $password = $this->passwordHasher->hashPassword($user, random_bytes(10));
            $user->setUsername('Florian' . random_int(0,1000));
            $user->setMail(random_bytes(7).'@test1.fr');
            $user->setPassword($password);
            $user->setRoles(null);
        }

        $manager->flush();
    }
}