<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
interface Html_Formatter {
	/**
	 * @param Html_Node $node
	 * @param callable():mixed $print_node
	 */
	public function print_node( $node, callable $print_node ): void;

	/**
	 * @param Html_Node $node
	 * @param callable():mixed $print_content
	 */
	public function print_node_content( $node, callable $print_content ): void;

	/**
	 * @param Html_Node $previous_node
	 * @param Html_Node $next_node
	 */
	public function print_interleave( $previous_node, $next_node ): void;
}
