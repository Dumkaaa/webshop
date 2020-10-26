<?php

namespace App\ActionLog;

interface LoggableObjectInterface
{
    public function getId(): ?int;

    /**
     * Returns the properties of the object that should not be logged.
     *
     * @return array<string>
     */
    public function getNonLoggableProperties(): array;
}
