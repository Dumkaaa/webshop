<?php

namespace App\Menu;

/**
 * Description of a single menu item with optional children.
 */
interface MenuItemInterface extends MenuInterface
{
    public function getIdentifier(): string;

    public function getLabel(): string;

    public function getUri(): string;

    public function isActive(): bool;

    public function getTarget(): ?string;

    public function getIcon(): ?string;

    public function getTranslationDomain(): ?string;
}
