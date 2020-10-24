<?php

namespace App\Menu;

/**
 * Base implementation of \App\Menu\MenuItemInterface::class.
 */
class MenuItem extends Menu implements MenuItemInterface
{
    private string $identifier;
    private string $label;
    private string $uri;
    private bool $isActive;
    private ?string $target;
    private ?string $icon;
    private ?string $translationDomain;

    /**
     * @param array<MenuItemInterface> $children
     */
    public function __construct(string $identifier, string $label, string $uri, bool $isActive = false, ?string $target = null, ?string $icon = null, ?string $translationDomain = null, array $children = [])
    {
        $this->identifier = $identifier;
        $this->label = $label;
        $this->uri = $uri;
        $this->isActive = $isActive;
        $this->target = $target;
        $this->icon = $icon;
        $this->translationDomain = $translationDomain;

        parent::__construct($children);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }
}
