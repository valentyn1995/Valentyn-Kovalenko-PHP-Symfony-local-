<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Profile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @group TestFixtures
 */
class TestFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }

    public function load(ObjectManager $objectManager): void
    {
        $this->userWithoutProfile($objectManager);
        $this->userWithProfile($objectManager);
    }

    private function userWithProfile($objectManager): void
    {
        $user2 = new User();
        $user2->setRoles(['ROLE_USER'])
            ->setLogin('testuser2')
            ->setEmail('test2@email.com')
            ->setPassword($this->passwordHasher->hashPassword($user2, '123456'))
            ->setConfirmed(true)
            ->setToken('12345678912345678913456789rwert');
        $objectManager->persist($user2);

        $profile2 = new Profile();
        $profile2->setUser($user2)
            ->setFirstName('Swiat')
            ->setLastName('Step')
            ->setAge('31')
            ->setBiography('Worker in storage')
            ->setAvatarName('testProf.png');
        $objectManager->persist($profile2);

        $objectManager->flush();
    }

    private function userWithoutProfile($objectManager): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER'])
            ->setLogin('testuser')
            ->setEmail('test@email.com')
            ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
            ->setConfirmed(true)
            ->setToken('12345678912345678913456789qwert');
        $objectManager->persist($user);

        $objectManager->flush();
    }
}
