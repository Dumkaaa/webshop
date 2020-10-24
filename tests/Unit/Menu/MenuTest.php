<?php

namespace App\Tests\Unit\Menu;

use App\Menu\Menu;
use App\Menu\MenuItem;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Menu\Menu
 */
class MenuTest extends TestCase
{
    public function testChildren(): void
    {
        $menu = new Menu([
            new MenuItem('foo', 'Foo', '/foo'),
            new MenuItem('bar', 'Bar', '/bar'),
        ]);

        $this->assertCount(2, $menu->getChildren());
        $this->assertSame(2, count($menu));
    }

    public function testArrayAccess(): void
    {
        $menu = new Menu();

        $this->assertCount(0, $menu);
        $this->assertFalse(isset($menu['foo']));

        $menuItem = new MenuItem('foo', 'Foo', '/foo');
        $menu['foo'] = $menuItem;

        $this->assertCount(1, $menu);
        $this->assertTrue(isset($menu['foo']));
        $this->assertSame($menuItem, $menu['foo']);

        unset($menu['foo']);

        $this->assertCount(0, $menu);
        $this->assertFalse(isset($menu['foo']));
    }
}
