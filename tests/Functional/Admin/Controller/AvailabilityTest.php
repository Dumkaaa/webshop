<?php

namespace App\Tests\Functional\Admin\Controller;

use App\DataFixtures\FixtureGroupInterface;
use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;

/**
 * @covers \App\Admin\Controller\AdminUserController
 * @covers \App\Admin\Controller\DashboardController
 * @covers \App\Admin\Controller\ProfileController
 * @covers \App\Admin\Controller\SecurityController
 */
class AvailabilityTest extends DoctrineFixturesTest
{
    private static bool $isFixturesLoaded = false;

    protected function loadFixtures(): void
    {
        /*
         * Make sure the fixtures are only loaded once.
         * The availability test does not rely on database changes, plus it would affect the test duration too much.
         */
        if (self::$isFixturesLoaded) {
            return;
        }

        self::$isFixturesLoaded = true;

        parent::loadFixtures();
    }

    protected function getFixtureGroups(): array
    {
        return [
            FixtureGroupInterface::ADMIN,
        ];
    }

    /**
     * @dataProvider superAdminUrlProvider
     * @dataProvider adminUrlProvider
     * @dataProvider userUrlProvider
     */
    public function testUnauthorizedRedirectToLogin(string $url): void
    {
        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * @dataProvider superAdminUrlProvider
     * @dataProvider adminUrlProvider
     * @dataProvider userUrlProvider
     */
    public function testAuthorizedSuperAdmin(string $url): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * @dataProvider superAdminUrlProvider
     */
    public function testUnauthorizedAdmin(string $url): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @dataProvider adminUrlProvider
     * @dataProvider userUrlProvider
     */
    public function testAuthorizedAdmin(string $url): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * @dataProvider adminUrlProvider
     */
    public function testUnauthorizedUser(string $url): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'user@example.com']);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @dataProvider userUrlProvider
     */
    public function testAuthorizedUser(string $url): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'user@example.com']);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * @return array<array<string>>
     */
    public function userUrlProvider(): array
    {
        return [
            // Dashboard
            ['/'],
            // Profile
            ['/profile'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function adminUrlProvider(): array
    {
        return [
            // Admin user
            ['/admin-users'],
            ['/admin-users/new'],
            ['/admin-users/edit/user@example.com'],
            ['/admin-users/edit/admin@example.com'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function superAdminUrlProvider(): array
    {
        return [
            // Admin user
            ['/admin-users/edit/superadmin@example.com'],
        ];
    }
}
