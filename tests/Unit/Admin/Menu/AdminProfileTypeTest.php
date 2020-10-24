<?php

namespace App\Tests\Unit\Admin\Menu;

use App\Admin\Menu\AdminProfileType;
use App\Tests\Unit\Menu\MenuTypeTest;

/**
 * @covers \App\Admin\Menu\AdminProfileType
 */
class AdminProfileTypeTest extends MenuTypeTest
{
    /**
     * @covers \App\Admin\Menu\AdminProfileType::getKey
     */
    public function testKey(): void
    {
        $this->assertSame('admin_profile', AdminProfileType::getKey());
    }

    /**
     * @covers \App\Admin\Menu\AdminProfileType::build
     */
    public function testBuild(): void
    {
        $this->assertBuild(new AdminProfileType(), [
            [
                'identifier' => 'profile',
                'label' => 'menu.profile',
                'route' => 'admin_profile_edit',
                'route_params' => [],
                'uri' => '/profile',
                'target' => null,
                'icon' => 'las la-user',
                'translation_domain' => 'messages',
                'children' => [],
            ],
            [
                'identifier' => 'logout',
                'label' => 'menu.logout',
                'route' => 'admin_logout',
                'route_params' => [],
                'uri' => '/logout',
                'target' => null,
                'icon' => 'las la-sign-out-alt',
                'translation_domain' => 'messages',
                'children' => [],
            ],
        ]);
    }
}
