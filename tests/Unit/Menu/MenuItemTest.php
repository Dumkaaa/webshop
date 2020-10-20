<?php

namespace App\Tests\Unit\Menu;

use App\Menu\MenuItem;
use PHPUnit\Framework\TestCase;

class MenuItemTest extends TestCase
{
    public function testDefaultConstruct(): void
    {
        $item = new MenuItem('identifier', 'label', 'uri');

        $this->assertSame('identifier', $item->getIdentifier());
        $this->assertSame('label', $item->getLabel());
        $this->assertSame('uri', $item->getUri());
        $this->assertFalse($item->isActive());
        $this->assertNull($item->getTarget());
        $this->assertNull($item->getIcon());
        $this->assertNull($item->getTranslationDomain());
        $this->assertCount(0, $item->getChildren());
    }

    public function testConstruct(): void
    {
        $item = new MenuItem('another_identifier', 'another_label', 'another_uri', true, '_blank', 'icon', 'messages', [
            new MenuItem('identifier', 'label', 'uri'),
        ]);

        $this->assertSame('another_identifier', $item->getIdentifier());
        $this->assertSame('another_label', $item->getLabel());
        $this->assertSame('another_uri', $item->getUri());
        $this->assertTrue($item->isActive());
        $this->assertSame('_blank', $item->getTarget());
        $this->assertSame('icon', $item->getIcon());
        $this->assertSame('messages', $item->getTranslationDomain());
        $this->assertCount(1, $item->getChildren());
    }
}
