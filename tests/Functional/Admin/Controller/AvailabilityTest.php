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
     * @dataProvider urlProvider
     */
    public function testUnauthorized(string $url): void
    {
        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * @dataProvider urlProvider
     */
    public function testAuthorized(string $url): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * @return array<array<string>>
     */
    public function urlProvider(): array
    {
        return [
            // Dashboard
            ['/'],
            // Profile
            ['/profile'],
        ];
    }
}
