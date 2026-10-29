<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

/**
 * @phpstan-type Html_Node string|Html_Element|Htmlable|Closure
 */
interface Html_Element {
	/**
	 * @return Html_Node[]
	 */
	public function get_nodes(): array;
}
