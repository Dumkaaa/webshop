<?php

namespace App\Menu;

class Menu implements MenuInterface
{
    /**
     * @var array<MenuItemInterface>
     */
    private array $children;

    /**
     * @param array<MenuItemInterface> $children
     */
    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     *
     * @param int|string $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->children[$offset]) || array_key_exists($offset, $this->children);
    }

    /**
     * {@inheritdoc}
     *
     * @param int|string $offset
     */
    public function offsetGet($offset): ?MenuItemInterface
    {
        return $this->children[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     *
     * @param int|string        $offset
     * @param MenuItemInterface $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->children[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param int|string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->children[$offset]);
    }

    public function count(): int
    {
        return count($this->children);
    }
}
