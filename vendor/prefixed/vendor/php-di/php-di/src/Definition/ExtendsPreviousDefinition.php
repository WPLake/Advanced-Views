<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\DI\Definition;

/**
 * A definition that extends a previous definition with the same name.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface ExtendsPreviousDefinition extends Definition
{
    public function setExtendedDefinition(Definition $definition);
}
