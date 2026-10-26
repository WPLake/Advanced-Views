<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Tag_Printer;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

/**
 * Wraps a printer, adding line breaks and tabs: the content of a tag goes on its own lines, one level deeper.
 * Breaks are leading only: every tag starts on its own line, except the first one of the output or of a content, which
 * already follows a break. Nothing is printed after the last tag, so the parent's closing tag starts its own line
 * without a blank one, and a break after the whole markup is up to the caller.
 * The printer is stateful, so use a new one per markup.
 */
final class Formatted_Tag_Printer implements Tag_Printer {
	protected Tag_Printer $printer;
	protected Html_Escaper $escaper;
	protected string $break_symbol;
	protected string $tab_symbol;
	protected int $depth;
	protected bool $is_first;

	public function __construct( Tag_Printer $printer, Html_Escaper $escaper ) {
		$this->printer      = $printer;
		$this->escaper      = $escaper;
		$this->break_symbol = "\r\n";
		$this->tab_symbol   = "\t";
		$this->depth        = 0;
		$this->is_first     = true;
	}

	public function print_pair_tag( Html_Tag $tag, Closure $print_content ): void {
		$this->print_break_before_tag();

		$this->printer->print_pair_tag(
			$tag,
			fn() => $this->print_indented( $print_content )
		);
	}

	public function print_single_tag( Html_Tag $tag ): void {
		$this->print_break_before_tag();

		$this->printer->print_single_tag( $tag );
	}

	public function set_break_symbol( string $break_symbol ): self {
		$this->break_symbol = $break_symbol;

		return $this;
	}

	public function set_tab_symbol( string $tab_symbol ): self {
		$this->tab_symbol = $tab_symbol;

		return $this;
	}

	protected function print_indented( Closure $print_content ): void {
		++$this->depth;
		$this->print_line_start();
		$this->is_first = true;

		$print_content();

		--$this->depth;
		$this->print_line_start();
		$this->is_first = false;
	}

	protected function print_break_before_tag(): void {
		if ( ! $this->is_first ) {
			$this->print_line_start();
		}

		$this->is_first = false;
	}

	protected function print_line_start(): void {
		$tabulation = str_repeat( $this->tab_symbol, $this->depth );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->escaper->esc_html( $this->break_symbol . $tabulation );
	}
}
