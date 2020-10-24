<?php

namespace App\Menu;

/**
 * Abstract menu type that generates the key based on the class name.
 */
abstract class AbstractMenuType implements MenuTypeInterface
{
    public static function getKey(): string
    {
        return MenuFactory::getTypeKeyForFullyQualifiedClassName(static::class);
    }
}
