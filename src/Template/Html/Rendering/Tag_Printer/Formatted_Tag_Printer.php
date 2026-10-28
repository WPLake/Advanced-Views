<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag_Printer;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Formatter;


final class Formatted_Tag_Printer implements Tag_Printer {
	protected Tag_Printer $printer;
	protected Html_Formatter $formatter;
	protected int $depth;
	protected bool $is_first;

	public function __construct( Tag_Printer $printer, Html_Formatter $formatter ) {
		$this->printer   = $printer;
		$this->formatter = $formatter;
		$this->depth     = 0;
		$this->is_first  = true;
	}

	public function print_pair_tag( Html_Tag $tag, Closure $print_content ): void {
		$this->print_break_before_node();

		$this->printer->print_pair_tag(
			$tag,
			fn() => $this->print_indented( $print_content )
		);
	}

	public function print_single_tag( Html_Tag $tag ): void {
		$this->print_break_before_node();

		$this->printer->print_single_tag( $tag );
	}

	protected function print_indented( Closure $print_content ): void {
		++$this->depth;
		$this->formatter->line_start( $this->depth );
		$this->is_first = true;

		$print_content();

		--$this->depth;
		$this->formatter->line_start( $this->depth );
		$this->is_first = false;
	}

	protected function print_break_before_node(): void {
		if ( ! $this->is_first ) {
			$this->formatter->line_start( $this->depth );
		}

		$this->is_first = false;
	}
}
