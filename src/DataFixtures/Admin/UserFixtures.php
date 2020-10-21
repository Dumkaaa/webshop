<?php

namespace App\DataFixtures\Admin;

use App\Entity\Admin\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Faker\ORM\Doctrine\Populator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private Generator $generator;

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

        // Add 100 fake users.
        $this->generator = Factory::create();
        $populator = new Populator($this->generator, $manager);

        $populator->addEntity(User::class, 100, [
            'emailAddress' => function () {
                return $this->generator->unique(true)->email;
            },
            'firstName' => function () {
                return $this->generator->firstName;
            },
            'lastName' => function () {
                return $this->generator->lastName;
            },
            'roles' => function () {
                // 1 in 10 chance it's an admin.
                return 1 === rand(1, 10) ? [User::ROLE_ADMIN] : [];
            },
            'isEnabled' => function () {
                // 1 in 10 chance it's disabled.
                return 1 !== rand(1, 10);
            },
            'password' => function () {
                return $this->generator->password;
            },
            'lastLoginAt' => function () {
                return $this->generator->optional(0.9)->dateTimeThisYear();
            },
            'lastActiveAt' => function () {
                return $this->generator->optional(0.9)->dateTimeThisYear();
            },
        ]);

        $populator->execute();

        $manager->flush();
    }
}
