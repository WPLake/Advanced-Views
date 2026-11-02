<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Nested;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;

/**
 * A token rendering only its content (get_nodes()), e.g. IF, whose branches are flattened into the nodes.
 */
interface Nested_Token extends Html_Element {
}
