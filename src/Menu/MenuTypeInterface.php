<?php

namespace App\Menu;

/**
 * A type of menu used to build menu's.
 */
interface MenuTypeInterface
{
    public static function getKey(): string;

    public function build(MenuBuilder $builder): void;
}
