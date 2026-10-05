<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\DI;

use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerExceptionInterface;
/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
