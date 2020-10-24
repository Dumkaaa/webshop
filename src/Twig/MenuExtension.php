<?php

namespace App\Twig;

use App\Menu\MenuFactory;
use App\Menu\MenuTypeInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for rendering menu's using the \App\Menu\MenuFactory::class service.
 */
class MenuExtension extends AbstractExtension
{
    private MenuFactory $factory;
    private Environment $environment;

    public function __construct(MenuFactory $factory, Environment $environment)
    {
        $this->factory = $factory;
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('render_menu', [$this, 'renderMenu'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param MenuTypeInterface|string $type
     */
    public function renderMenu($type, string $template): string
    {
        $menu = $this->factory->create($type);

        return $this->environment->render($template, [
            'menu' => $menu,
        ]);
    }
}
