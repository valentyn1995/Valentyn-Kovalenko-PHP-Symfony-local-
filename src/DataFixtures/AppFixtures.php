<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Profile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

/**
 * @group AppFixtures
 */
class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }

    public function load(ObjectManager $objectManager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $user = $this->userFakes($faker);
            $profile = $this->profileFakes($faker);

            $objectManager->persist($user);
            $objectManager->persist($profile);

            $profile->setUser($user);
            $user->setProfile($profile);

            $objectManager->flush();
        }
    }

    private function userFakes($faker): User
    {
        $login = $faker->userName;
        $email = $faker->email;
        $password = $faker->password;
        $token = $faker->sentence(1);

        $user = new User();
        $user->setRoles(['ROLE_USER'])
            ->setLogin($login)
            ->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($user, $password))
            ->setConfirmed(true)
            ->setToken($token);

        return $user;
    }

    private function profileFakes($faker): Profile
    {
        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $age = $faker->numberBetween(1, 115);
        $biography = $faker->sentence(5);

        $profile = new Profile();
        $profile->setFirstName($firstName)
            ->setLastName($lastName)
            ->setAge($age)
            ->setBiography($biography);

        return $profile;
    }
}
