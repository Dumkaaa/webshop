<?php

namespace App\Tests\Functional\Admin\Controller;

use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;

class AvailabilityTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            'UserFixtures',
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

//    /**
//     * @dataProvider superAdminUrlProvider
//     */
//    public function testUnauthorizedAdmin(string $url): void
//    {
//        /** @var UserRepository $userRepository */
//        $userRepository = static::$container->get(UserRepository::class);
//        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
//
//        $this->client->loginUser($user, 'admin');
//        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);
//
//        $this->assertResponseStatusCodeSame(403);
//    }

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
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function superAdminUrlProvider(): array
    {
        return [];
    }
}
