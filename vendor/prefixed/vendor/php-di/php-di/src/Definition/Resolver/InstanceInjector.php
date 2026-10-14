<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\DI\Definition\Resolver;

use Org\Wplake\Advanced_Views\Vendors\DI\Definition\Definition;
use Org\Wplake\Advanced_Views\Vendors\DI\Definition\InstanceDefinition;
use Org\Wplake\Advanced_Views\Vendors\DI\DependencyException;
use Org\Wplake\Advanced_Views\Vendors\Psr\Container\NotFoundExceptionInterface;
/**
 * Injects dependencies on an existing instance.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InstanceInjector extends ObjectCreator
{
    /**
     * Injects dependencies on an existing instance.
     *
     * @param InstanceDefinition $definition
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        try {
            $this->injectMethodsAndProperties($definition->getInstance(), $definition->getObjectDefinition());
        } catch (NotFoundExceptionInterface $e) {
            $message = \sprintf('Error while injecting dependencies into %s: %s', \get_class($definition->getInstance()), $e->getMessage());
            throw new DependencyException($message, 0, $e);
        }
        return $definition;
    }
    public function isResolvable(Definition $definition, array $parameters = []) : bool
    {
        return \true;
    }
}
