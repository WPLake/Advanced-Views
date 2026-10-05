<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\DI\Definition\Helper;

use Org\Wplake\Advanced_Views\Vendors\DI\Definition\Definition;
/**
 * Helps defining container entries.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionHelper
{
    /**
     * @param string $entryName Container entry name
     */
    public function getDefinition(string $entryName) : Definition;
}
