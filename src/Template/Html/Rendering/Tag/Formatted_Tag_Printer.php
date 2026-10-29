<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Formatter;

final class Formatted_Tag_Printer implements Tag_Printer {
	protected Tag_Printer $printer;
	protected Html_Formatter $formatter;

	public function __construct( Tag_Printer $printer, Html_Formatter $formatter ) {
		$this->printer   = $printer;
		$this->formatter = $formatter;
	}

	public function print_pair_tag( Html_Tag $tag, ?callable $print_content = null ): void {
		$print_formatted = is_callable( $print_content ) ?
			fn() => $this->print_content( $print_content ) :
			null;

		$this->printer->print_pair_tag(
			$tag,
			$print_formatted
		);
	}

	public function print_single_tag( Html_Tag $tag ): void {
		$this->printer->print_single_tag( $tag );
	}

	/**
	 * @param callable():mixed $print_content
	 */
	protected function print_content( callable $print_content ): void {
		$this->formatter->new_line()
						->tab_shift( $print_content )
						->indent_line();
	}
}
