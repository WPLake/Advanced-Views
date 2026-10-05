<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\Invoker\ParameterResolver\Container;

use Org\Wplake\Advanced_Views\Vendors\Invoker\ParameterResolver\ParameterResolver;
use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;
/**
 * Inject entries from a DI container using the parameter names.
 */
class ParameterNameContainerResolver implements ParameterResolver
{
    /** @var ContainerInterface */
    private $container;
    /**
     * @param ContainerInterface $container The container to get entries from.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function getParameters(ReflectionFunctionAbstract $reflection, array $providedParameters, array $resolvedParameters) : array
    {
        $parameters = $reflection->getParameters();
        // Skip parameters already resolved
        if (!empty($resolvedParameters)) {
            $parameters = \array_diff_key($parameters, $resolvedParameters);
        }
        foreach ($parameters as $index => $parameter) {
            $name = $parameter->name;
            if ($name && $this->container->has($name)) {
                $resolvedParameters[$index] = $this->container->get($name);
            }
        }
        return $resolvedParameters;
    }
}
