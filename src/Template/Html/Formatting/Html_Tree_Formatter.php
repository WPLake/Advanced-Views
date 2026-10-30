<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

use Closure;

class Html_Tree_Formatter implements Html_Formatter {
	protected Text_Formatter $formatter;

	public function __construct( Text_Formatter $formatter ) {
		$this->formatter = $formatter;
	}

	public function format_node( $node, string $node_html ): string {
		return $node_html;
	}

	public function format_node_content( $node, Closure $render_content ): string {
		$new_line        = $this->formatter->new_line();
		$shifted_content = $this->formatter->tab_shift( $render_content );
		$indented_end    = $this->formatter->indent_line();

		$content_parts = array(
			$new_line,
			$shifted_content,
			$indented_end,
		);

		return join( '', $content_parts );
	}

	public function format_interleave( $previous_node, $next_node ): string {
		return $this->formatter->new_line();
	}
}
