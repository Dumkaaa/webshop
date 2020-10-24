<?php

namespace App\Menu;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Builder used to help with creating menu's.
 */
class MenuBuilder
{
    private RouterInterface $router;
    private ?string $activeRoute;
    private MenuInterface $menu;

    public function __construct(RouterInterface $router, ?string $activeRoute = null, ?MenuInterface $menu = null)
    {
        $this->router = $router;
        $this->activeRoute = $activeRoute;
        $this->menu = $menu ?? new Menu();
    }

    /**
     * Creates a new MenuItemInterface and adds it to this builder's menu.
     *
     * @param MenuBuilder|MenuItemInterface|string $child
     * @param array<mixed>                         $options
     */
    public function add($child, array $options = []): self
    {
        if ($child instanceof MenuBuilder) {
            $child = $child->getMenu();
            if (!$child instanceof MenuItemInterface) {
                throw new \InvalidArgumentException('A root MenuBuilder instance cannot be added as a child.');
            }
        } elseif (!$child instanceof MenuItemInterface) {
            $child = $this->createItem($child, $options);
        }

        $this->menu[$child->getIdentifier()] = $child;

        return $this;
    }

    /**
     * Creates a new MenuItemInterface and creates a new MenuBuilder instance for it.
     * NOTE: The MenuItemInterface will not be added to the menu of this builder.
     *
     * @param array<mixed> $options
     */
    public function create(string $identifier, array $options): MenuBuilder
    {
        $item = $this->createItem($identifier, $options);

        return new MenuBuilder($this->router, $this->activeRoute, $item);
    }

    /**
     * Creates a new MenuItemInterface.
     *
     * @param array<mixed> $options
     */
    private function createItem(string $identifier, array $options): MenuItemInterface
    {
        $options = $this->resolveOptions($options);

        $uri = $options['uri'] ?? $this->router->generate($options['route'], $options['route_params']);
        $isActive = $options['active'];
        if (null === $isActive && $options['active_pattern'] && $this->activeRoute) {
            $isActive = 1 === preg_match($options['active_pattern'], $this->activeRoute);
        }

        return new MenuItem(
            $identifier,
            $options['label'] ?? $identifier, // Fallback the label to the identifier.
            $uri,
            $isActive ?? false,
            $options['target'],
            $options['icon'],
            $options['translation_domain'],
        );
    }

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'label' => null,
            'uri' => null,
            'route' => null,
            'route_params' => [],
            'active' => null,
            'active_pattern' => null,
            'target' => null,
            'icon' => null,
            'translation_domain' => 'messages',
        ]);

        $resolver->setAllowedTypes('label', ['string', 'null']);
        $resolver->setAllowedTypes('uri', ['string', 'null']);
        $resolver->setAllowedTypes('route', ['string', 'null']);
        $resolver->setAllowedTypes('route_params', 'array');
        $resolver->setAllowedTypes('active', ['bool', 'null']);
        $resolver->setAllowedTypes('active_pattern', ['string', 'null']);
        $resolver->setAllowedTypes('target', ['string', 'null']);
        $resolver->setAllowedTypes('icon', ['string', 'null']);
        $resolver->setAllowedTypes('translation_domain', ['string', 'null']);

        $options = $resolver->resolve($options);

        // Make sure 'uri' or 'route' is set, and not both.
        if ($options['uri'] && $options['route']) {
            throw new InvalidOptionsException('"uri" and "route" cannot both be set.');
        } elseif (!$options['uri'] && !$options['route']) {
            throw new InvalidOptionsException('"uri" or "route" must be set.');
        }

        // Make sure active and active_pattern are not both set.
        if (null !== $options['active'] && $options['active_pattern']) {
            throw new InvalidOptionsException('"active" and "active_pattern" cannot both be set.');
        }

        return $options;
    }

    public function getMenu(): MenuInterface
    {
        return $this->menu;
    }
}
