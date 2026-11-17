<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Section_Token;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Tree_Formatter;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Nested_Token;

/**
 * Sections (the parts of loops and conditions) are laid out like tag content: wrapped by new lines and shifted.
 * Nested tokens (loops, IF) add no level of their own, their nodes are formatted as the nodes of the parent level,
 * but they are set apart by an empty line above and below (so two nested tokens in a row have two between them).
 */
final class Token_Tree_Formatter extends Tree_Formatter {
	public function format_node( $node, string $node_html ): string {
		if ( $node instanceof Nested_Token ) {
			return $this->set_apart( $node_html );
		}

		return parent::format_node( $node, $node_html );
	}

	public function format_node_content( $node, Closure $render_content ): string {
		if ( $node instanceof Nested_Token ) {
			return $render_content();
		}

		if ( $node instanceof Section_Token ) {
			return $this->format_unclosed_content( $render_content );
		}

		return parent::format_node_content( $node, $render_content );
	}

	protected function set_apart( string $node_html ): string {
		$new_line = $this->formatter->new_line();

		return $new_line . $node_html . $new_line;
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
