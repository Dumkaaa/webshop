<?php

namespace App\Tests\Functional\Admin\Controller;

use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;

class AdminUserControllerTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            'UserFixtures',
        ];
    }

    public function testPagination(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', '/admin-users', [], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Showing 1 to 10 of 104 results', $content);

        $this->client->request('GET', '/admin-users', [
            'page' => 2,
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Showing 11 to 20 of 104 results', $content);
    }

    public function testSorting(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', '/admin-users', [], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Name<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Email address<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Last active at<i class="las la-arrow-up"></i>', $content);
        $this->assertStringContainsString('Enabled<i class="las la-arrows-alt-v"></i>', $content);

        $this->client->request('GET', '/admin-users', [
            'sort' => 'u.firstName',
            'direction' => 'asc',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Name<i class="las la-arrow-down"></i>', $content);
        $this->assertStringContainsString('Email address<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Last active at<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Enabled<i class="las la-arrows-alt-v"></i>', $content);

        $this->client->request('GET', '/admin-users', [
            'sort' => 'u.emailAddress',
            'direction' => 'desc',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Name<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Email address<i class="las la-arrow-up"></i>', $content);
        $this->assertStringContainsString('Last active at<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Enabled<i class="las la-arrows-alt-v"></i>', $content);

        $this->client->request('GET', '/admin-users', [
            'sort' => 'u.lastActiveAt',
            'direction' => 'asc',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Name<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Email address<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Last active at<i class="las la-arrow-down"></i>', $content);
        $this->assertStringContainsString('Enabled<i class="las la-arrows-alt-v"></i>', $content);

        $this->client->request('GET', '/admin-users', [
            'sort' => 'u.isEnabled',
            'direction' => 'asc',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Name<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Email address<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Last active at<i class="las la-arrows-alt-v"></i>', $content);
        $this->assertStringContainsString('Enabled<i class="las la-arrow-down"></i>', $content);
    }

    public function testSearching(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', '/admin-users', [
            'q' => 'admin@example.com',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Showing 1 to 2 of 2 results', $content);
    }
}
