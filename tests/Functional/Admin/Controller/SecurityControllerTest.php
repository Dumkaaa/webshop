<?php

namespace App\Tests\Functional\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SecurityControllerTest extends WebTestCase
{
    public function testLoadLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testLogin(): void
    {
        $client = static::createClient();
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        $client->request('POST', '/login', [
            'emailAddress' => 'admin@example.com',
            'password' => 'P4$$w0rd',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseRedirects('/');
    }

    /**
     * Test login after being redirected because the user was unauthorized to visit a page.
     */
    public function testLoginFromRedirect(): void
    {
        $client = static::createClient();
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        $client->request('GET', '/', [], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();

        $client->submitForm('Sign in', [
            'emailAddress' => 'admin@example.com',
            'password' => 'P4$$w0rd',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ]);

        // The login page should redirect back to the page that redirected to the login page.
        $this->assertResponseRedirects('http://admin.webshop.test/');
    }

    public function testInvalidEmailAddress(): void
    {
        $client = static::createClient();
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        $client->request('POST', '/login', [
            'emailAddress' => 'unknown@example.com',
            'password' => 'P4$$w0rd',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $client->followRedirect();
        $this->assertSelectorTextContains('html form div.alert-danger', 'Email address could not be found.');
    }

    public function testInvalidPassword(): void
    {
        $client = static::createClient();
        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = static::$container->get(CsrfTokenManagerInterface::class);

        $client->request('POST', '/login', [
            'emailAddress' => 'admin@example.com',
            'password' => 'invalidpassword',
            '_csrf_token' => $csrfTokenManager->getToken('authenticate'),
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $client->followRedirect();
        $this->assertSelectorTextContains('html form div.alert-danger', 'Invalid credentials.');
    }

    public function testInvalidCsrfToken(): void
    {
        $client = static::createClient();

        $client->request('POST', '/login', [
            'emailAddress' => 'admin@example.com',
            'password' => 'test11',
        ], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $client->followRedirect();
        $this->assertSelectorTextContains('html form div.alert-danger', 'Invalid CSRF token.');
    }

    public function testLogout(): void
    {
        $client = static::createClient();

        $client->request('GET', '/logout', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $this->assertResponseStatusCodeSame(302);
    }
}
