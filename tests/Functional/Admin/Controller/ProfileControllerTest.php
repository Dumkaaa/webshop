<?php

namespace App\Tests\Functional\Admin\Controller;

use App\DataFixtures\FixtureGroupInterface;
use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;
use Symfony\Component\DomCrawler\Field\FormField;

/**
 * @covers \App\Admin\Controller\ProfileController
 * @covers \App\Admin\Form\ProfileType
 */
class ProfileControllerTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            FixtureGroupInterface::ADMIN_USER,
        ];
    }

    /**
     * @covers \App\Admin\Controller\ProfileController::view
     */
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

    /**
     * @covers \App\Admin\Controller\ProfileController::view
     */
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

    /**
     * @covers \App\Admin\Controller\ProfileController::edit
     * @covers \App\Admin\Form\ProfileType
     */
    public function testUpdateProfile(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/profile', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $form = $crawler->selectButton('Save')->form();

        $this->assertInstanceOf(FormField::class, $firstPasswordField = $form['profile[plainPassword][first]']);
        $this->assertInstanceOf(FormField::class, $secondPasswordField = $form['profile[plainPassword][second]']);

        $firstPasswordField->setValue('new_password');
        $secondPasswordField->setValue('new_password');

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(302);
        $this->client->followRedirect();

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Edit profile', $content);
        $this->assertStringContainsString('Profile saved', $content);
    }

    /**
     * @covers \App\Admin\Controller\ProfileController::edit
     * @covers \App\Admin\Form\ProfileType
     */
    public function testUpdateProfileInvalid(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/profile', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $form = $crawler->selectButton('Save')->form();

        $this->assertInstanceOf(FormField::class, $firstPasswordField = $form['profile[plainPassword][first]']);
        $this->assertInstanceOf(FormField::class, $secondPasswordField = $form['profile[plainPassword][second]']);

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
