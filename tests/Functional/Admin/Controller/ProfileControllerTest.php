<?php

namespace App\Tests\Functional\Admin\Controller;

use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;
use Symfony\Component\DomCrawler\Field\FormField;

class ProfileControllerTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            'UserFixtures',
        ];
    }

    public function testOtherProfile(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', '/profile/user@example.com', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Foo Bar&#039;s profile', $content);
        $this->assertStringNotContainsString('Edit profile', $content);
    }

    public function testProfileNotFound(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', '/profile/foo@bar.com', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testUpdateProfile(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/profile', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $form = $crawler->selectButton('Save')->form();

        $firstPasswordField = $form['profile[plainPassword][first]'];
        $secondPasswordField = $form['profile[plainPassword][second]'];
        $this->assertInstanceOf(FormField::class, $firstPasswordField);
        $this->assertInstanceOf(FormField::class, $secondPasswordField);

        $firstPasswordField->setValue('new_password');
        $secondPasswordField->setValue('new_password');

        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Edit profile', $content);
        $this->assertStringContainsString('Profile saved', $content);
    }

    public function testUpdateProfileInvalidPassword(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/profile', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $form = $crawler->selectButton('Save')->form();

        $firstPasswordField = $form['profile[plainPassword][first]'];
        $secondPasswordField = $form['profile[plainPassword][second]'];
        $this->assertInstanceOf(FormField::class, $firstPasswordField);
        $this->assertInstanceOf(FormField::class, $secondPasswordField);

        $firstPasswordField->setValue('new_password');
        $secondPasswordField->setValue('other_password');

        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Check the form for errors', $content);
        $this->assertStringContainsString('The password fields must match', $content);

        $firstPasswordField->setValue('short');
        $secondPasswordField->setValue('short');

        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Check the form for errors', $content);
        $this->assertStringContainsString('This value is too short. It should have 6 characters or more.', $content);
    }
}
