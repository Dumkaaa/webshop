<?php

namespace App\Tests\Functional\Admin\Controller;

use App\DataFixtures\FixtureGroupInterface;
use App\Repository\Admin\UserRepository;
use App\Tests\Functional\DoctrineFixturesTest;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;

/**
 * @covers \App\Admin\Controller\AdminUserController
 * @covers \App\Admin\Form\AdminUserType
 */
class AdminUserControllerTest extends DoctrineFixturesTest
{
    protected function getFixtureGroups(): array
    {
        return [
            FixtureGroupInterface::ADMIN_USER,
        ];
    }

    /**
     * @covers \App\Admin\Controller\AdminUserController::index
     */
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

    /**
     * @covers \App\Admin\Controller\AdminUserController::index
     */
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

    /**
     * @covers \App\Admin\Controller\AdminUserController::index
     */
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

    /**
     * @covers \App\Admin\Controller\AdminUserController::create
     * @covers \App\Admin\Form\AdminUserType
     */
    public function testCreate(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/admin-users/new', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $form = $crawler->selectButton('Create')->form();

        $this->assertInstanceOf(FormField::class, $firstNameField = $form['admin_user[firstName]']);
        $this->assertInstanceOf(FormField::class, $lastNameField = $form['admin_user[lastName]']);
        $this->assertInstanceOf(FormField::class, $emailAddressField = $form['admin_user[emailAddress]']);
        $this->assertInstanceOf(ChoiceFormField::class, $isEnabledField = $form['admin_user[isEnabled]']);
        $this->assertInstanceOf(ChoiceFormField::class, $rolesField = $form['admin_user[roles]']);
        $this->assertInstanceOf(FormField::class, $firstPasswordField = $form['admin_user[plainPassword][first]']);
        $this->assertInstanceOf(FormField::class, $secondPasswordField = $form['admin_user[plainPassword][second]']);

        $firstNameField->setValue('Foo');
        $lastNameField->setValue('Bar');
        $emailAddressField->setValue('foo@bar.com');
        $isEnabledField->tick();
        $rolesField->tick();
        $firstPasswordField->setValue('new_password');
        $secondPasswordField->setValue('new_password');

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(302);
        $this->client->followRedirect();

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('foo@bar.com', $content);
        $this->assertStringContainsString('User created', $content);
    }

    /**
     * @covers \App\Admin\Controller\AdminUserController::create
     * @covers \App\Admin\Form\AdminUserType
     */
    public function testCreateInvalid(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/admin-users/new', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $form = $crawler->selectButton('Create')->form();

        $this->assertInstanceOf(FormField::class, $firstNameField = $form['admin_user[firstName]']);
        $this->assertInstanceOf(FormField::class, $lastNameField = $form['admin_user[lastName]']);
        $this->assertInstanceOf(FormField::class, $emailAddressField = $form['admin_user[emailAddress]']);
        $this->assertInstanceOf(ChoiceFormField::class, $isEnabledField = $form['admin_user[isEnabled]']);
        $this->assertInstanceOf(ChoiceFormField::class, $rolesField = $form['admin_user[roles]']);
        $this->assertInstanceOf(FormField::class, $firstPasswordField = $form['admin_user[plainPassword][first]']);
        $this->assertInstanceOf(FormField::class, $secondPasswordField = $form['admin_user[plainPassword][second]']);

        $firstNameField->setValue('Foo');
        $lastNameField->setValue('Bar');
        $emailAddressField->setValue('admin@example.com');
        $isEnabledField->tick();
        $rolesField->tick();
        $firstPasswordField->setValue('new_password');
        $secondPasswordField->setValue('other_password');

        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Check the form for errors', $content);
        $this->assertStringContainsString('This value is already used.', $content);
        $this->assertStringContainsString('The password fields must match', $content);

        $firstPasswordField->setValue('short');
        $secondPasswordField->setValue('short');

        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Check the form for errors', $content);
        $this->assertStringContainsString('This value is too short. It should have 6 characters or more.', $content);
    }

    /**
     * @covers \App\Admin\Controller\AdminUserController::create
     * @covers \App\Admin\Form\AdminUserType
     */
    public function testCreateFieldsAsAdmin(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'admin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/admin-users/new', [], [], ['HTTP_HOST' => 'admin.webshop.test']);
        $form = $crawler->selectButton('Create')->form();

        $this->assertInstanceOf(FormField::class, $firstNameField = $form['admin_user[firstName]']);
        $this->assertInstanceOf(FormField::class, $lastNameField = $form['admin_user[lastName]']);
        $this->assertInstanceOf(FormField::class, $emailAddressField = $form['admin_user[emailAddress]']);
        $this->assertInstanceOf(ChoiceFormField::class, $isEnabledField = $form['admin_user[isEnabled]']);
        $this->assertInstanceOf(FormField::class, $firstPasswordField = $form['admin_user[plainPassword][first]']);
        $this->assertInstanceOf(FormField::class, $secondPasswordField = $form['admin_user[plainPassword][second]']);

        $this->assertFalse($form->has('admin_user[roles]'));
    }

    /**
     * @covers \App\Admin\Controller\AdminUserController::edit
     * @covers \App\Admin\Form\AdminUserType
     */
    public function testEdit(): void
    {
        $this->submitEditForm();
    }

    /**
     * @covers \App\Admin\Controller\AdminUserController::edit
     * @covers \App\Admin\Form\AdminUserType
     */
    public function testEditNewPassword(): void
    {
        $this->submitEditForm(true);
    }

    private function submitEditForm(bool $newPassword = false): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/admin-users/edit/admin@example.com', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $form = $crawler->selectButton('Save')->form();

        $this->assertInstanceOf(FormField::class, $firstNameField = $form['admin_user[firstName]']);
        $this->assertInstanceOf(FormField::class, $lastNameField = $form['admin_user[lastName]']);
        $this->assertInstanceOf(FormField::class, $emailAddressField = $form['admin_user[emailAddress]']);
        $this->assertInstanceOf(ChoiceFormField::class, $isEnabledField = $form['admin_user[isEnabled]']);
        $this->assertInstanceOf(ChoiceFormField::class, $rolesField = $form['admin_user[roles]']);
        $this->assertInstanceOf(FormField::class, $firstPasswordField = $form['admin_user[plainPassword][first]']);
        $this->assertInstanceOf(FormField::class, $secondPasswordField = $form['admin_user[plainPassword][second]']);

        $firstNameField->setValue('Foo');
        $lastNameField->setValue('Bar');
        $emailAddressField->setValue('admin@example.com');
        $isEnabledField->tick();
        $rolesField->tick();
        if ($newPassword) {
            $firstPasswordField->setValue('new_password');
            $secondPasswordField->setValue('new_password');
        }

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(302);
        $this->client->followRedirect();

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('admin@example.com', $content);
        $this->assertStringContainsString('User saved', $content);
    }

    /**
     * @covers \App\Admin\Controller\AdminUserController::edit
     * @covers \App\Admin\Form\AdminUserType
     */
    public function testEditInvalid(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['emailAddress' => 'superadmin@example.com']);
        $this->client->loginUser($user, 'admin');

        $crawler = $this->client->request('GET', '/admin-users/edit/admin@example.com', [], [], ['HTTP_HOST' => 'admin.webshop.test']);

        $form = $crawler->selectButton('Save')->form();

        $this->assertInstanceOf(FormField::class, $firstNameField = $form['admin_user[firstName]']);
        $this->assertInstanceOf(FormField::class, $lastNameField = $form['admin_user[lastName]']);
        $this->assertInstanceOf(FormField::class, $emailAddressField = $form['admin_user[emailAddress]']);
        $this->assertInstanceOf(ChoiceFormField::class, $isEnabledField = $form['admin_user[isEnabled]']);
        $this->assertInstanceOf(ChoiceFormField::class, $rolesField = $form['admin_user[roles]']);
        $this->assertInstanceOf(FormField::class, $firstPasswordField = $form['admin_user[plainPassword][first]']);
        $this->assertInstanceOf(FormField::class, $secondPasswordField = $form['admin_user[plainPassword][second]']);

        $firstNameField->setValue('Foo');
        $lastNameField->setValue('Bar');
        $emailAddressField->setValue('user@example.com');
        $isEnabledField->tick();
        $rolesField->tick();
        $firstPasswordField->setValue('new_password');
        $secondPasswordField->setValue('other_password');

        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Check the form for errors', $content);
        $this->assertStringContainsString('This value is already used.', $content);
        $this->assertStringContainsString('The password fields must match', $content);

        $firstPasswordField->setValue('short');
        $secondPasswordField->setValue('short');

        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Check the form for errors', $content);
        $this->assertStringContainsString('This value is too short. It should have 6 characters or more.', $content);
    }

    /**
     * @covers \App\Admin\Controller\AdminUserController::bulkEnable
     */
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

    /**
     * @covers \App\Admin\Controller\AdminUserController::bulkDisable
     */
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
