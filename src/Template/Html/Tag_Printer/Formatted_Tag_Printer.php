<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Tag_Printer;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

/**
 * Wraps a printer, adding line breaks and tabs: the content of a tag goes on its own lines, one level deeper.
 * After every tag the tabs of the next sibling are printed, so the first top-level tag isn't indented.
 */
final class Formatted_Tag_Printer implements Tag_Printer {
	protected Tag_Printer $printer;
	protected string $break_symbol;
	protected string $tab_symbol;
	protected int $depth;

	public function __construct( Tag_Printer $printer ) {
		$this->printer      = $printer;
		$this->break_symbol = "\r\n";
		$this->tab_symbol   = "\t";
		$this->depth        = 0;
	}

	public function print_pair_tag( Html_Tag $tag, Closure $print_content ): void {
		$this->printer->print_pair_tag(
			$tag,
			fn() => $this->print_indented( $print_content )
		);

		$this->print_line_start();
	}

	public function print_single_tag( Html_Tag $tag ): void {
		$this->printer->print_single_tag( $tag );

		$this->print_line_start();
	}

	public function set_break_symbol( string $break_symbol ): self {
		$this->break_symbol = $break_symbol;

		return $this;
	}

	public function set_tab_symbol( string $tab_symbol ): self {
		$this->tab_symbol = $tab_symbol;

		return $this;
	}

	public function set_depth( int $depth ): self {
		$this->depth = $depth;

		return $this;
	}

	protected function print_indented( Closure $print_content ): void {
		++$this->depth;
		$this->print_line_start();

		$print_content();

		--$this->depth;
		$this->print_line_start();
	}

	protected function print_line_start(): void {
		$tabulation = str_repeat( $this->tab_symbol, $this->depth );

		echo esc_html( $this->break_symbol . $tabulation );
	}
}
