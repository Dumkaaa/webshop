<?php

namespace App\Admin\Menu;

use App\Menu\AbstractMenuType;
use App\Menu\MenuBuilder;

class AdminProfileType extends AbstractMenuType
{
    public function build(MenuBuilder $builder): void
    {
        $builder
            ->add('logout', [
                'label' => 'menu.logout',
                'route' => 'admin_logout',
                'icon' => 'fas fa-sign-out-alt',
            ])
        ;
    }
}
