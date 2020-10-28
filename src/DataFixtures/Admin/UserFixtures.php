<?php

namespace App\DataFixtures\Admin;

use App\DataFixtures\FixtureGroupInterface;
use App\Entity\Admin\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Faker\ORM\Doctrine\Populator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Fixtures for \App\Entity\Admin\User::class.
 */
class UserFixtures extends Fixture implements FixtureGroupInterface
{
    const REFERENCE_SUPER_ADMIN = 'user_super_admin';
    const REFERENCE_ADMIN = 'user_admin';
    const REFERENCE_USER = 'user_user';
    const REFERENCE_DISABLED = 'user_super_disabled';

    private UserPasswordEncoderInterface $passwordEncoder;
    private Generator $generator;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getGroups(): array
    {
        return [
            FixtureGroupInterface::ADMIN,
            FixtureGroupInterface::ADMIN_USER,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $superAdmin = new User();
        $superAdmin->setEmailAddress('superadmin@example.com');
        $superAdmin->setFirstName('Super');
        $superAdmin->setLastName('Admin');
        $superAdmin->setRoles([User::ROLE_SUPER_ADMIN]);
        $superAdmin->setIsEnabled(true);
        $superAdmin->setPassword($this->passwordEncoder->encodePassword($superAdmin, 'P4$$w0rd'));
        $manager->persist($superAdmin);
        $this->setReference(self::REFERENCE_SUPER_ADMIN, $superAdmin);

        $admin = new User();
        $admin->setEmailAddress('admin@example.com');
        $admin->setFirstName('First Name');
        $admin->setLastName('Last Name');
        $admin->setRoles([User::ROLE_ADMIN]);
        $admin->setIsEnabled(true);
        $admin->setPassword($this->passwordEncoder->encodePassword($admin, 'P4$$w0rd'));
        $manager->persist($admin);
        $this->setReference(self::REFERENCE_ADMIN, $admin);

        $user = new User();
        $user->setEmailAddress('user@example.com');
        $user->setFirstName('Foo');
        $user->setLastName('Bar');
        $user->setIsEnabled(true);
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'P4$$w0rd'));
        $manager->persist($user);
        $this->setReference(self::REFERENCE_USER, $user);

        $disabledUser = new User();
        $disabledUser->setEmailAddress('disabled@example.com');
        $disabledUser->setFirstName('Disabled');
        $disabledUser->setLastName('User');
        $disabledUser->setIsEnabled(false);
        $disabledUser->setPassword($this->passwordEncoder->encodePassword($disabledUser, 'P4$$w0rd'));
        $manager->persist($disabledUser);
        $this->setReference(self::REFERENCE_DISABLED, $disabledUser);

        // Add 100 fake users.
        $this->generator = Factory::create();
        $populator = new Populator($this->generator, $manager);

        $populator->addEntity(User::class, 100, [
            'emailAddress' => function () {
                return $this->generator->unique()->email;
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
