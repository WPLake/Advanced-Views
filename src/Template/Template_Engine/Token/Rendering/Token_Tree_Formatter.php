<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Html_Tree_Formatter;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Nested_Token;

/**
 * Block tokens (loops) are laid out like tags: their content is wrapped by new lines and shifted.
 * Nested tokens (IF) are transparent: their nodes are formatted as the nodes of the parent level.
 */
final class Token_Tree_Formatter extends Html_Tree_Formatter {
	public function format_node( $node, string $node_html ): string {
		return $node instanceof Nested_Token ?
			$node_html :
			parent::format_node( $node, $node_html );
	}

	public function format_node_content( $node, Closure $render_content ): string {
		return $node instanceof Nested_Token ?
			$render_content() :
			parent::format_node_content( $node, $render_content );
	}
}
