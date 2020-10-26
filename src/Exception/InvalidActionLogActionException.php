<?php

namespace App\Exception;

/**
 * Thrown when an unsupported action is used for an \App\Entity\ActionLog::class.
 */
class InvalidActionLogActionException extends \InvalidArgumentException implements ActionLogExceptionInterface
{
    public function __construct(string $action)
    {
        parent::__construct(sprintf('Invalid action "%s".', $action));
    }
}
