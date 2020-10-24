<?php

namespace App\Menu;

use ArrayAccess;
use Countable;

/**
 * Collection for other \App\Menu\MenuInterface::class instances.
 */
interface MenuInterface extends Countable, ArrayAccess
{
    /**
     * @return array<MenuInterface>
     */
    public function getChildren(): array;
}
