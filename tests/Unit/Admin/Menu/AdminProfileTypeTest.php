<?php

namespace App\Tests\Unit\Admin\Menu;

use App\Admin\Menu\AdminProfileType;
use App\Tests\Unit\Menu\MenuTypeTest;

class AdminProfileTypeTest extends MenuTypeTest
{
    public function testKey(): void
    {
        $this->assertSame('admin_profile', AdminProfileType::getKey());
    }

    public function testBuild(): void
    {
        $this->assertBuild(new AdminProfileType(), [
            [
                'identifier' => 'profile',
                'label' => 'menu.profile',
                'route' => 'admin_profile',
                'route_params' => [],
                'uri' => '/profile',
                'target' => null,
                'icon' => 'fas fa-user',
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
                'icon' => 'fas fa-sign-out-alt',
                'translation_domain' => 'messages',
                'children' => [],
            ],
        ]);
    }
}
