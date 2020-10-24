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

    public function testBulkEnable(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $superAdminUser = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);
        $disabledUser = $userRepository->findOneBy(['emailAddress' => 'disabled@example.com']);
        $this->client->loginUser($superAdminUser, 'admin');

        // Test with invalid parameters.
        $this->client->request('GET', '/admin-users/bulk-enable', [], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $this->assertResponseStatusCodeSame(400);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Provide comma separated email addresses via the &quot;values&quot; GET parameter.', $content);

        // Test enabling the disabled user, pass multiple email addresses to make sure the comma separated value works.
        $this->assertFalse($disabledUser->isEnabled());
        $this->client->request('GET', '/admin-users/bulk-enable', [
            'values' => 'nonexisting@user.com,disabled@example.com',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/admin-users');
        // Refresh the disabled user, it should now be enabled.
        $disabledUser = $userRepository->findOneBy(['emailAddress' => 'disabled@example.com']);
        $this->assertTrue($disabledUser->isEnabled());

        // Test referer.
        $this->client->request('GET', '/admin-users/bulk-enable', [
            'values' => 'nonexisting@user.com,disabled@example.com',
            'referer' => '/',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/');
    }

    public function testBulkDisable(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $superAdminUser = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);
        $adminUser = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $defaultUser = $userRepository->findOneBy(['emailAddress' => 'user@example.com']);
        $this->client->loginUser($superAdminUser, 'admin');

        // Test with invalid parameters.
        $this->client->request('GET', '/admin-users/bulk-disable', [], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $this->assertResponseStatusCodeSame(400);
        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Provide comma separated email addresses via the &quot;values&quot; GET parameter.', $content);

        // Test disabling the admin and default users.
        $this->assertTrue($adminUser->isEnabled());
        $this->assertTrue($defaultUser->isEnabled());
        $this->client->request('GET', '/admin-users/bulk-disable', [
            'values' => 'nonexisting@user.com,admin@example.com,user@example.com',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/admin-users');
        // Refresh the users, they should now be disabled.
        $adminUser = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $defaultUser = $userRepository->findOneBy(['emailAddress' => 'user@example.com']);
        $this->assertFalse($adminUser->isEnabled());
        $this->assertFalse($defaultUser->isEnabled());

        // Test referer.
        $this->client->request('GET', '/admin-users/bulk-disable', [
            'values' => 'nonexisting@user.com,admin@example.com,user@example.com',
            'referer' => '/',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/');
    }
}
