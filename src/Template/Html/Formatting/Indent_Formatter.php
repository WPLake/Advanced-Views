<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

final class Indent_Formatter implements Html_Formatter {
	protected Text_Formatter $formatter;

	public function __construct( Text_Formatter $formatter ) {
		$this->formatter = $formatter;
	}

	public function print_block_content( callable $print_content ): void {
		$this->formatter->new_line()
						->tab_shift( $print_content )
						->indent_line();
	}

	public function print_block( callable $print_element ): void {
		$print_element();
		$this->formatter->new_line();
	}
}
