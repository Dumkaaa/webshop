<?php

namespace App\Exception;

use Symfony\Component\Translation\Exception\ExceptionInterface;

/**
 * Thrown when a menu type could not be found.
 */
class MenuTypeNotFoundException extends \InvalidArgumentException implements ExceptionInterface
{
}
