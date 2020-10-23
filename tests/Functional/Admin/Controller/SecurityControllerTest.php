<?php

namespace App\Tests\Functional\Admin\Controller;

use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SecurityControllerTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            'UserFixtures',
        ];
    }

    public function testLoadLogin(): void
    {
        $this->client->request('GET', '/login', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testLogin(): void
    {
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);

        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->assertNotNull($user);
        $this->assertNull($user->getLastLoginAt());

        $this->client->request('POST', '/login', [
            'emailAddress' => 'admin@example.com',
            'password' => 'P4$$w0rd',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertNotNull($user->getLastLoginAt());
        $this->assertEquals((new \DateTimeImmutable())->format('d-m-Y'), $user->getLastLoginAt()->format('d-m-Y'));

        $this->assertResponseRedirects('/');
    }

    /**
     * Test login after being redirected because the user was unauthorized to visit a page.
     */
    public function testLoginFromRedirect(): void
    {
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        $this->client->request('GET', '/', [], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $this->assertResponseStatusCodeSame(302);
        $this->client->followRedirect();

        $this->client->submitForm('Sign in', [
            'emailAddress' => 'admin@example.com',
            'password' => 'P4$$w0rd',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ]);

        // The login page should redirect back to the page that redirected to the login page.
        $this->assertResponseRedirects('http://admin.webshop.test/');
    }

    public function testInvalidEmailAddress(): void
    {
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        $this->client->request('POST', '/login', [
            'emailAddress' => 'unknown@example.com',
            'password' => 'P4$$w0rd',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->client->followRedirect();
        $this->assertSelectorTextContains('html form div.alert-danger', 'Email address could not be found.');
    }

    public function testDisabledAccount(): void
    {
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        $this->client->request('POST', '/login', [
            'emailAddress' => 'disabled@example.com',
            'password' => 'P4$$w0rd',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->client->followRedirect();
        $this->assertSelectorTextContains('html form div.alert-danger', 'Account is disabled.');
    }

    public function testInvalidPassword(): void
    {
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        $this->client->request('POST', '/login', [
            'emailAddress' => 'admin@example.com',
            'password' => 'invalidpassword',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->client->followRedirect();
        $this->assertSelectorTextContains('html form div.alert-danger', 'Invalid credentials.');
    }

    public function testInvalidCsrfToken(): void
    {
        $this->client->request('POST', '/login', [
            'emailAddress' => 'admin@example.com',
            'password' => 'test11',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->client->followRedirect();
        $this->assertSelectorTextContains('html form div.alert-danger', 'Invalid CSRF token.');
    }

    public function testLogout(): void
    {
        $this->client->request('GET', '/logout', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(302);
    }
}
