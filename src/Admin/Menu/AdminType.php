<?php

namespace App\Admin\Menu;

use App\Menu\AbstractMenuType;
use App\Menu\MenuBuilder;

class AdminType extends AbstractMenuType
{
    public function build(MenuBuilder $builder): void
    {
        $builder
            ->add('dashboard', [
                'label' => 'menu.dashboard',
                'route' => 'admin_dashboard',
                'active_pattern' => '/admin_dashboard/',
                'icon' => 'las la-home',
            ])
        ;
    }
}
