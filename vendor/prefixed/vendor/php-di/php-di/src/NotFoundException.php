<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\DI;

use Org\Wplake\Advanced_Views\Vendors\Psr\Container\NotFoundExceptionInterface;
/**
 * Exception thrown when a class or a value is not found in the container.
 */
class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
