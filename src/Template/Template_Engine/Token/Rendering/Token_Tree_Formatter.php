<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Block\Block_Token;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Html_Tree_Formatter;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Nested_Token;

/**
 * Block tokens (loops, condition branches) are laid out like tags: their content is wrapped by new lines and shifted.
 * Nested tokens (IF) are transparent: their nodes are formatted as the nodes of the parent level.
 */
final class Token_Tree_Formatter extends Html_Tree_Formatter {
	public function format_node( $node, string $node_html ): string {
		return $node instanceof Nested_Token ?
			$node_html :
			parent::format_node( $node, $node_html );
	}

	public function format_node_content( $node, Closure $render_content ): string {
		if ( $node instanceof Nested_Token ) {
			return $render_content();
		}

		if ( $node instanceof Block_Token &&
			0 === strlen( $node->get_close_statement() ) ) {
			return $this->format_unclosed_content( $render_content );
		}

		return parent::format_node_content( $node, $render_content );
	}

	/**
	 * Without the closing part there is nothing after the content, the next sibling starts on its own line
	 */
	protected function format_unclosed_content( Closure $render_content ): string {
		$new_line        = $this->formatter->new_line();
		$shifted_content = $this->formatter->tab_shift( $render_content );

		return $new_line . $shifted_content;
	}
}
