<?php

namespace App\Tests\Unit\Twig;

use App\Menu\Menu;
use App\Menu\MenuFactory;
use App\Twig\MenuExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Node\Node;

/**
 * @covers \App\Twig\MenuExtension
 */
class MenuExtensionTest extends TestCase
{
    /**
     * @covers \App\Twig\MenuExtension::getFunctions
     */
    public function testGetFunctions(): void
    {
        $factory = $this->prophesize(MenuFactory::class)->reveal();
        $environment = $this->prophesize(Environment::class)->reveal();

        $extension = new MenuExtension($factory, $environment);

        $functions = $extension->getFunctions();

        $this->assertCount(1, $functions);

        $function = $functions[0];

        $this->assertSame('render_menu', $function->getName());
        $callable = $function->getCallable();
        $this->assertIsArray($callable);
        $this->assertSame('renderMenu', $callable[1]);
        $this->assertSame(['html'], $function->getSafe(new Node()));
    }

    /**
     * @covers \App\Twig\MenuExtension::renderMenu
     */
    public function testRenderMenu(): void
    {
        $menu = new Menu();

        $factoryProphecy = $this->prophesize(MenuFactory::class);
        $factoryProphecy->create('foo')
            ->shouldBeCalledTimes(1)
            ->willReturn($menu);

        $environmentProphecy = $this->prophesize(Environment::class);
        $environmentProphecy->render('template.html.twig', ['menu' => $menu])
            ->shouldBeCalledTimes(1)
            ->willReturn('Menu succesfully passed to the template, leaves the rest to the frontend! :)');

        $extension = new MenuExtension($factoryProphecy->reveal(), $environmentProphecy->reveal());

        $renderedMenu = $extension->renderMenu('foo', 'template.html.twig');
        $this->assertSame('Menu succesfully passed to the template, leaves the rest to the frontend! :)', $renderedMenu);
    }
}
