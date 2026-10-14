<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\DI\Definition\Source;

use Org\Wplake\Advanced_Views\Vendors\DI\Definition\Definition;
use Org\Wplake\Advanced_Views\Vendors\DI\Definition\Exception\InvalidDefinition;
/**
 * Source of definitions for entries of the container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionSource
{
    /**
     * Returns the DI definition for the entry name.
     *
     * @throws InvalidDefinition An invalid definition was found.
     * @return Definition|null
     */
    public function getDefinition(string $name);
    /**
     * @return Definition[] Definitions indexed by their name.
     */
    public function getDefinitions() : array;
}
