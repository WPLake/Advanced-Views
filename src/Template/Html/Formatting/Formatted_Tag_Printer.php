<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Tag_Printer\Tag_Printer;

/**
 * Wraps a printer, adding line breaks and tabs: the content of a tag goes on its own lines, one level deeper.
 * Breaks are leading only: every node (a tag, text, a callback) starts on its own line, except the first one of the
 * output or of a content, which already follows a break. Nothing is printed after the last tag, so the parent's closing tag starts its own line
 * without a blank one, and a break after the whole markup is up to the caller.
 * The printer is stateful, so use a new one per markup.
 */
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
