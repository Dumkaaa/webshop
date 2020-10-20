<?php

namespace App\Tests\Unit\Menu;

use App\Menu\MenuBuilder;
use App\Menu\MenuInterface;
use App\Menu\MenuItemInterface;
use App\Menu\MenuTypeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Routing\RouterInterface;

abstract class MenuTypeTest extends TestCase
{
    /**
     * @param array<array<mixed>> $expectedItems
     */
    protected function assertBuild(MenuTypeInterface $type, array $expectedItems): void
    {
        $routerProphecy = $this->prophesize(RouterInterface::class);

        $this->fillUriGenerateProphecy($routerProphecy, $expectedItems);

        $builder = new MenuBuilder($routerProphecy->reveal());
        $type->build($builder);

        $menu = $builder->getMenu();

        $this->assertMenuItems($expectedItems, $menu);
    }

    /**
     * @param ObjectProphecy<RouterInterface> $prophecy
     * @param array<array<mixed>>             $expectedItems
     */
    private function fillUriGenerateProphecy(ObjectProphecy $prophecy, array $expectedItems): void
    {
        foreach ($expectedItems as $item) {
            if (isset($item['route'])) {
                $prophecy->generate($item['route'], $item['route_params'])->shouldBeCalledTimes(1)->willReturn($item['uri']);
            }
            $this->fillUriGenerateProphecy($prophecy, $item['children']);
        }
    }

    /**
     * @param array<array<mixed>> $expectedItems
     */
    private function assertMenuItems(array $expectedItems, MenuInterface $menu): void
    {
        foreach ($expectedItems as $item) {
            /** @var MenuItemInterface $menuItem */
            $menuItem = $menu[$item['identifier']];

            $this->assertSame($item['identifier'], $menuItem->getIdentifier());
            $this->assertSame($item['label'], $menuItem->getLabel());
            $this->assertSame($item['uri'], $menuItem->getUri());
            $this->assertFalse($menuItem->isActive());
            $this->assertSame($item['target'], $menuItem->getTarget());
            $this->assertSame($item['icon'], $menuItem->getIcon());
            $this->assertSame($item['translation_domain'], $menuItem->getTranslationDomain());
            $this->assertCount(count($item['children']), $menuItem->getChildren());

            $this->assertMenuItems($item['children'], $menuItem);
        }
    }
}
