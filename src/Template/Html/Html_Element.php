<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-type Html_Node string|Html_Element
 */
interface Html_Element {
	/**
	 * @return Html_Node[]
	 */
	public function get_nodes(): array;
}
