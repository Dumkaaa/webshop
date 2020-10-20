<?php

namespace App\Menu;

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
