<?php

namespace App\Menu;

use ArrayAccess;
use Countable;

interface MenuInterface extends Countable, ArrayAccess
{
    /**
     * @return array<MenuInterface>
     */
    public function getChildren(): array;
}
