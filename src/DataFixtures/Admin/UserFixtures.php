<?php

namespace App\DataFixtures\Admin;

use App\Entity\Admin\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmailAddress('admin@example.com');
        $admin->setFirstName('First Name');
        $admin->setLastName('Last Name');
        $admin->setRoles([User::ROLE_ADMIN]);
        $admin->setIsEnabled(true);
        $admin->setPassword($this->passwordEncoder->encodePassword($admin, 'P4$$w0rd'));
        $manager->persist($admin);

        $user = new User();
        $user->setEmailAddress('user@example.com');
        $user->setFirstName('Foo');
        $user->setLastName('Bar');
        $user->setIsEnabled(true);
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'P4$$w0rd'));
        $manager->persist($user);

        $manager->flush();
    }
}
