<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

class Html_Tree_Formatter implements Html_Formatter {
	protected Text_Formatter $formatter;

	public function __construct( Text_Formatter $formatter ) {
		$this->formatter = $formatter;
	}

	public function print_node( $node, callable $print_node ): void {
		$print_node();
	}

	public function print_node_content( $node, callable $print_content ): void {
		$this->formatter->new_line()
						->tab_shift( $print_content )
						->indent_line();
	}

	public function print_interleave( $previous_node, $next_node ): void {
		$this->formatter->new_line();
	}
}
