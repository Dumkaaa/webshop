<?php

namespace App\Tests\Functional\Admin\Controller;

use App\Repository\Admin\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AvailabilityTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testUnauthorized(string $url): void
    {
        $client = static::createClient();

        $client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * @dataProvider urlProvider
     */
    public function testAuthorized(string $url): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);

        $client->loginUser($user, 'admin');
        $client->request('GET', $url, [], [], ['HTTP_HOST' => 'admin.webshop.test']);

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
