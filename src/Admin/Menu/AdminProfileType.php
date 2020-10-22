<?php

namespace App\Admin\Menu;

use App\Menu\AbstractMenuType;
use App\Menu\MenuBuilder;

class AdminProfileType extends AbstractMenuType
{
    public function build(MenuBuilder $builder): void
    {
        $builder
            ->add('profile', [
                'label' => 'menu.profile',
                'route' => 'admin_profile_edit',
                'active_pattern' => '/admin_profile_edit/',
                'icon' => 'las la-user',
            ])
            ->add('logout', [
                'label' => 'menu.logout',
                'route' => 'admin_logout',
                'icon' => 'las la-sign-out-alt',
            ])
        ;
    }
}
