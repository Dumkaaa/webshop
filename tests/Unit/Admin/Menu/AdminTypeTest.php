<?php

namespace App\Tests\Unit\Admin\Menu;

use App\Admin\Menu\AdminType;
use App\Entity\Admin\User;
use App\Tests\Unit\Menu\MenuTypeTest;
use Symfony\Component\Security\Core\Security;

/**
 * @covers \App\Admin\Menu\AdminType
 */
class AdminTypeTest extends MenuTypeTest
{
    /**
     * @covers \App\Admin\Menu\AdminType::getKey
     */
    public function testKey(): void
    {
        $this->assertSame('admin', AdminType::getKey());
    }

    /**
     * @covers \App\Admin\Menu\AdminType::build
     */
    public function testBuildAdminUser(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted(User::ROLE_ADMIN)->shouldBeCalledTimes(1)->willReturn(true);

        $this->assertBuild(new AdminType($securityProphecy->reveal()), array_merge($this->getDefaultExpectedItems(), [
            [
                'identifier' => 'admin_user',
                'label' => 'menu.admin_user',
                'route' => 'admin_admin_user_index',
                'route_params' => [],
                'uri' => '/admin-users',
                'target' => null,
                'icon' => 'las la-user-shield',
                'translation_domain' => 'messages',
                'children' => [],
            ],
        ]));
    }

    /**
     * @covers \App\Admin\Menu\AdminType::build
     */
    public function testBuildDefaultUser(): void
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted(User::ROLE_ADMIN)->shouldBeCalledTimes(1)->willReturn(false);

        $this->assertBuild(new AdminType($securityProphecy->reveal()), $this->getDefaultExpectedItems());
    }

    /**
     * @return array<array<mixed>>
     */
    private function getDefaultExpectedItems(): array
    {
        return [
            [
                'identifier' => 'dashboard',
                'label' => 'menu.dashboard',
                'route' => 'admin_dashboard',
                'route_params' => [],
                'uri' => '/',
                'target' => null,
                'icon' => 'las la-home',
                'translation_domain' => 'messages',
                'children' => [],
            ],
        ];
    }
}
