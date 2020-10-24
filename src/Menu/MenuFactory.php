<?php

namespace App\Menu;

use App\Exception\MenuTypeNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Traversable;

/**
 * Factory for \App\Menu\MenuInterface::class based on the given \App\Menu\MenuTypeInterface::class.
 */
class MenuFactory
{
    /** @var array<MenuTypeInterface> */
    private array $types;
    private RouterInterface $router;
    private RequestStack $requestStack;

    /**
     * @param Traversable<MenuTypeInterface> $types All services tagged with app.menu_type
     */
    public function __construct(Traversable $types, RouterInterface $router, RequestStack $requestStack)
    {
        $this->types = iterator_to_array($types);
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * @param MenuTypeInterface|string $type
     *
     * @throws MenuTypeNotFoundException
     */
    public function create($type): MenuInterface
    {
        if (!$type instanceof MenuTypeInterface) {
            $type = $this->guessType($type);
        }

        $request = $this->requestStack->getCurrentRequest();
        $activeRoute = $request ? $request->attributes->get('_route', null) : null;

        $builder = new MenuBuilder($this->router, $activeRoute);

        $type->build($builder);

        return $builder->getMenu();
    }

    /**
     * Try to find a menu type based on the service key or (fully qualified) class name.
     *
     * @throws MenuTypeNotFoundException
     */
    public function guessType(string $key): MenuTypeInterface
    {
        // Find by service key.
        if (isset($this->types[$key])) {
            return $this->types[$key];
        }

        // Check if it's a fully qualified class name, if so transform 'FooType' to 'foo'.
        $type = self::getTypeKeyForFullyQualifiedClassName($key);
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        // Find by class name as a last resort.
        foreach ($this->types as $type) {
            if (get_class($type) === $key) {
                return $type;
            }
        }

        throw new MenuTypeNotFoundException(sprintf('No menu type found for "%s"', $key));
    }

    public static function getTypeKeyForFullyQualifiedClassName(string $className): string
    {
        if (false !== strpos(strtolower($className), 'type') && preg_match('~([^\\\\]+?)(type)?$~i', $className, $matches)) {
            return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], $matches[1]));
        }

        return $className;
    }
}
