<?php

namespace App\Admin\Menu;

use App\Entity\Admin\User;
use App\Menu\AbstractMenuType;
use App\Menu\MenuBuilder;
use Symfony\Component\Security\Core\Security;

class AdminType extends AbstractMenuType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function build(MenuBuilder $builder): void
    {
        $isAdmin = $this->security->isGranted(User::ROLE_ADMIN);

        $builder->add('dashboard', [
            'label' => 'menu.dashboard',
            'route' => 'admin_dashboard',
            'active_pattern' => '/admin_dashboard/',
            'icon' => 'las la-home',
        ]);

        if ($isAdmin) {
            $builder->add('admin_user', [
                'label' => 'menu.admin_user',
                'route' => 'admin_admin_user_index',
                'active_pattern' => '/admin_admin_user_(.*)/',
                'icon' => 'las la-user-shield',
            ]);
        }
    }
}
