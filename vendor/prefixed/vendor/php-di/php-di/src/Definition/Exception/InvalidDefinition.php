<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\DI\Definition\Exception;

use Org\Wplake\Advanced_Views\Vendors\DI\Definition\Definition;
use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerExceptionInterface;
/**
 * Invalid DI definitions.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InvalidDefinition extends \Exception implements ContainerExceptionInterface
{
    public static function create(Definition $definition, string $message, \Exception $previous = null) : self
    {
        return new self(\sprintf('%s' . \PHP_EOL . 'Full definition:' . \PHP_EOL . '%s', $message, (string) $definition), 0, $previous);
    }
}
