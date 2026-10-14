<?php

declare (strict_types=1);
namespace Org\Wplake\Advanced_Views\Vendors\DI\Compiler;

use Org\Wplake\Advanced_Views\Vendors\DI\Factory\RequestedEntry;
/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RequestedEntryHolder implements RequestedEntry
{
    /**
     * @var string
     */
    private $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name;
    }
}
