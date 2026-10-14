<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\Invoker;

use Org\Wplake\Advanced_Views\Vendors\Invoker\Exception\InvocationException;
use Org\Wplake\Advanced_Views\Vendors\Invoker\Exception\NotCallableException;
use Org\Wplake\Advanced_Views\Vendors\Invoker\Exception\NotEnoughParametersException;
/**
 * Invoke a callable.
 */
interface InvokerInterface
{
    /**
     * Call the given function using the given parameters.
     *
     * @param callable|array|string $callable Function to call.
     * @param array $parameters Parameters to use.
     * @return mixed Result of the function.
     * @throws InvocationException Base exception class for all the sub-exceptions below.
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function call($callable, array $parameters = []);
}
