<?php

namespace App\Tests\Functional\DataFixtures\Admin;

use App\DataFixtures\Admin\UserFixtures;
use App\Entity\Admin\User;
use App\Repository\Admin\UserRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @covers \App\DataFixtures\Admin\UserFixtures
 */
class UserFixturesTest extends WebTestCase
{
    protected function setUp(): void
    {
        $client = static::createClient();

        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        // Reset the database.
        $application->run(new StringInput('doctrine:database:drop --force --quiet'));
        $application->run(new StringInput('doctrine:database:create --quiet'));
        $application->run(new StringInput('doctrine:schema:create --quiet'));

        parent::setUp();
    }

    /**
     * @covers \App\DataFixtures\Admin\UserFixtures::load
     */
    public function testFixturesWithoutReferenceRepository(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container->get(EntityManagerInterface::class);
        /** @var UserPasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = static::$container->get(UserPasswordEncoderInterface::class);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function setReference() on null');

        $fixtures = new UserFixtures($passwordEncoder);
        $fixtures->load($entityManager);
    }

    /**
     * @covers \App\DataFixtures\Admin\UserFixtures::load
     */
    public function testFixtures(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container->get(EntityManagerInterface::class);
        /** @var UserPasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = static::$container->get(UserPasswordEncoderInterface::class);
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);

        $users = $userRepository->findAll();
        $this->assertCount(0, $users);

        $referenceRepository = new ReferenceRepository($entityManager);
        $fixtures = new UserFixtures($passwordEncoder);
        $fixtures->setReferenceRepository($referenceRepository);
        $fixtures->load($entityManager);

        $users = $userRepository->findAll();
        $this->assertCount(104, $users);

        $superAdmin = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);
        $this->assertNotNull($superAdmin);
        $this->assertCount(2, $superAdmin->getRoles());
        $this->assertSame(User::ROLE_SUPER_ADMIN, $superAdmin->getRoles()[0]);
        $this->assertSame(User::ROLE_USER, $superAdmin->getRoles()[1]);
        $this->assertTrue($superAdmin->isEnabled());
        $this->assertSame('Super', $superAdmin->getFirstName());
        $this->assertSame('Admin', $superAdmin->getLastName());

        $admin = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->assertNotNull($admin);
        $this->assertCount(2, $admin->getRoles());
        $this->assertSame(User::ROLE_ADMIN, $admin->getRoles()[0]);
        $this->assertSame(User::ROLE_USER, $admin->getRoles()[1]);
        $this->assertTrue($admin->isEnabled());
        $this->assertSame('First Name', $admin->getFirstName());
        $this->assertSame('Last Name', $admin->getLastName());

        $user = $userRepository->findOneBy(['emailAddress' => 'user@example.com']);
        $this->assertNotNull($user);
        $this->assertCount(1, $user->getRoles());
        $this->assertSame(User::ROLE_USER, $user->getRoles()[0]);
        $this->assertTrue($user->isEnabled());
        $this->assertSame('Foo', $user->getFirstName());
        $this->assertSame('Bar', $user->getLastName());

        $disabledUser = $userRepository->findOneBy(['emailAddress' => 'disabled@example.com']);
        $this->assertNotNull($disabledUser);
        $this->assertCount(1, $disabledUser->getRoles());
        $this->assertSame(User::ROLE_USER, $disabledUser->getRoles()[0]);
        $this->assertFalse($disabledUser->isEnabled());
        $this->assertSame('Disabled', $disabledUser->getFirstName());
        $this->assertSame('User', $disabledUser->getLastName());
    }
}
