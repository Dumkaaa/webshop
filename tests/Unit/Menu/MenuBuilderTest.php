<?php

namespace App\Tests\Unit\Menu;

use App\Menu\Menu;
use App\Menu\MenuBuilder;
use App\Menu\MenuItem;
use App\Menu\MenuItemInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Routing\RouterInterface;

class MenuBuilderTest extends TestCase
{
    public function testAddExistingChild(): void
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $builder = new MenuBuilder($router);

        $item = new MenuItem('identifier', 'label', 'uri');
        $child = new MenuBuilder($router, null, $item);
        $builder->add($child);

        $this->assertSame($item, $builder->getMenu()['identifier']);
    }

    public function testAddExistingRootChild(): void
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $builder = new MenuBuilder($router);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A root MenuBuilder instance cannot be added as a child.');

        $child = new MenuBuilder($router);
        $builder->add($child);
    }

    public function testAddNewChildWithMinimalOptions(): void
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $builder = new MenuBuilder($router);

        $builder->add('foo_identifier', ['uri' => '/foo']);
        $fooItem = $builder->getMenu()['foo_identifier'];

        $this->assertSame('foo_identifier', $fooItem->getIdentifier());
        $this->assertSame('foo_identifier', $fooItem->getLabel());
        $this->assertSame('/foo', $fooItem->getUri());
        $this->assertFalse($fooItem->isActive());
        $this->assertNull($fooItem->getTarget());
        $this->assertNull($fooItem->getIcon());
        $this->assertSame('messages', $fooItem->getTranslationDomain());
        $this->assertCount(0, $fooItem->getChildren());
    }

    public function testAddNewChildWithMaximumOptions(): void
    {
        $routerProphecy = $this->prophesize(RouterInterface::class);
        $routerProphecy->generate('active_route', [
            'param1' => 'foo',
            'param2' => 'bar',
        ])->shouldBeCalledTimes(1)->willReturn('/prophesized-uri');

        $builder = new MenuBuilder($routerProphecy->reveal(), 'active_route');

        $builder->add('foo_identifier', [
            'label' => 'label',
            'uri' => null,
            'route' => 'active_route',
            'route_params' => [
                'param1' => 'foo',
                'param2' => 'bar',
            ],
            'active' => null,
            'active_pattern' => '/active_route/',
            'target' => '_blank',
            'icon' => 'icon',
            'translation_domain' => 'translation_domain',
        ]);
        $fooItem = $builder->getMenu()['foo_identifier'];

        $this->assertSame('foo_identifier', $fooItem->getIdentifier());
        $this->assertSame('label', $fooItem->getLabel());
        $this->assertSame('/prophesized-uri', $fooItem->getUri());
        $this->assertTrue($fooItem->isActive());
        $this->assertSame('_blank', $fooItem->getTarget());
        $this->assertSame('icon', $fooItem->getIcon());
        $this->assertSame('translation_domain', $fooItem->getTranslationDomain());
        $this->assertCount(0, $fooItem->getChildren());
    }

    public function testAddNewChildWithoutUriAndRoute(): void
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $builder = new MenuBuilder($router);

        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('"uri" and "route" cannot both be set.');
        $builder->add('test', [
            'uri' => '/test',
            'route' => 'test',
        ]);
    }

    public function testAddNewChildWithUriAndRoute(): void
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $builder = new MenuBuilder($router);

        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('"uri" or "route" must be set.');
        $builder->add('test');
    }

    public function testAddNewChildWithActiveAndActivePattern(): void
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $builder = new MenuBuilder($router);

        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('"active" and "active_pattern" cannot both be set.');
        $builder->add('test', [
            'uri' => 'test',
            'active' => true,
            'active_pattern' => '/test/',
        ]);
    }

    public function testCreate(): void
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $builder = new MenuBuilder($router);

        $childBuilder = $builder->create('foo_identifier', ['uri' => '/foo']);

        $this->assertInstanceOf(MenuItemInterface::class, $childBuilder->getMenu());
        $this->assertSame('foo_identifier', $childBuilder->getMenu()->getIdentifier());
    }

    public function testGetMenu(): void
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $menu = new Menu();

        $builder = new MenuBuilder($router, null, $menu);

        $this->assertSame($menu, $builder->getMenu());
    }
}
