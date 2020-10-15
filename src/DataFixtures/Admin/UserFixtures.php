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

        $admin->setCreatedAt(new \DateTime());
        $admin->setLastUpdatedAt(new \DateTime());

        $manager->persist($admin);

        $manager->flush();
    }
}
