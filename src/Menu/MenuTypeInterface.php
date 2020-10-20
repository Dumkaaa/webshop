<?php

namespace App\Menu;

interface MenuTypeInterface
{
    public static function getKey(): string;

    public function build(MenuBuilder $builder): void;
}
