<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
interface Html_Formatter {
	/**
	 * @param Html_Node $node
	 */
	public function format_node( $node, string $node_html ): string;

	/**
	 * @param Html_Node $node
	 * @param Closure():string $render_content
	 */
	public function format_node_content( $node, Closure $render_content ): string;

	/**
	 * @param Html_Node $previous_node
	 * @param Html_Node $next_node
	 */
	public function format_interleave( $previous_node, $next_node ): string;
}
