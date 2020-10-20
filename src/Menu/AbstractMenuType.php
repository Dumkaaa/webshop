<?php

namespace App\Menu;

abstract class AbstractMenuType implements MenuTypeInterface
{
    public static function getKey(): string
    {
        return MenuFactory::getTypeKeyForFullyQualifiedClassName(static::class);
    }
}
