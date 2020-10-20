<?php

namespace App\Tests\Unit\Admin\Menu;

use App\Admin\Menu\AdminType;
use App\Tests\Unit\Menu\MenuTypeTest;

class AdminTypeTest extends MenuTypeTest
{
    public function testKey(): void
    {
        $this->assertSame('admin', AdminType::getKey());
    }

    public function testBuild(): void
    {
        $this->assertBuild(new AdminType(), [
            [
                'identifier' => 'dashboard',
                'label' => 'menu.dashboard',
                'route' => 'admin_dashboard',
                'route_params' => [],
                'uri' => '/',
                'target' => null,
                'icon' => 'fas fa-home',
                'translation_domain' => 'messages',
                'children' => [],
            ],
        ]);
    }
}
