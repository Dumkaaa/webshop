<?php

namespace App\Tests\Unit\Menu;

use App\Exception\MenuTypeNotFoundException;
use App\Menu\MenuBuilder;
use App\Menu\MenuFactory;
use App\Menu\MenuTypeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class MenuFactoryTest extends TestCase
{
    public function testCreateByTypeKey(): void
    {
        $typeProphecy = $this->prophesize(MenuTypeInterface::class);
        $typeProphecy->build(Argument::type(MenuBuilder::class))->will(function ($args) {
            $args[0]->add('foo', ['uri' => '/foo']);
        });
        $type = $typeProphecy->reveal();

        $types = new \ArrayIterator([
            'foo' => $type,
        ]);

        $routerProphecy = $this->prophesize(RouterInterface::class);
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $factory = new MenuFactory($types, $routerProphecy->reveal(), $requestStackProphecy->reveal());

        $menu = $factory->create('foo');

        $this->assertCount(1, $menu);
    }

    public function testCreateByTypeInstance(): void
    {
        $typeProphecy = $this->prophesize(MenuTypeInterface::class);
        $types = new \ArrayIterator();
        $routerProphecy = $this->prophesize(RouterInterface::class);
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $factory = new MenuFactory($types, $routerProphecy->reveal(), $requestStackProphecy->reveal());

        $menu = $factory->create($typeProphecy->reveal());

        $this->assertCount(0, $menu);
    }

    public function testGuessType(): void
    {
        $typeProphecy = $this->prophesize(MenuTypeInterface::class);
        $type = $typeProphecy->reveal();

        $types = new \ArrayIterator([
            'foo' => $type,
        ]);

        $routerProphecy = $this->prophesize(RouterInterface::class);
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $factory = new MenuFactory($types, $routerProphecy->reveal(), $requestStackProphecy->reveal());

        $this->assertSame($type, $factory->guessType('foo'));
        $this->assertSame($type, $factory->guessType('App\\Menu\\FooType'));
        $this->assertSame($type, $factory->guessType(get_class($type)));

        $this->expectException(MenuTypeNotFoundException::class);
        $factory->guessType('bar');
    }

    public function testGetTypeKeyForFullyQualifiedClassName(): void
    {
        $this->assertSame('foo', MenuFactory::getTypeKeyForFullyQualifiedClassName('App\\Menu\\FooType'));

        $this->assertSame('foo', MenuFactory::getTypeKeyForFullyQualifiedClassName('foo'));
    }
}
