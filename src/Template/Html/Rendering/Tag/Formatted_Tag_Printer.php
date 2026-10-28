<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag;

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

	/**
	 * Also used for the content that isn't a tag, but is block-like, e.g. the body of a template condition or loop
	 */
	public function print_indented( Closure $print_content ): void {
		++$this->depth;
		$this->formatter->line_start( $this->depth );
		$this->is_first = true;

		$print_content();

		--$this->depth;
		$this->formatter->line_start( $this->depth );
		$this->is_first = false;
	}

	/**
	 * For the manual formatting around non-tag nodes: the next tag is considered as the first one in the line
	 */
	public function print_line_start(): void {
		$this->formatter->line_start( $this->depth );
		$this->is_first = true;
	}

	public function print_tabs(): void {
		$this->formatter->tab( $this->depth );
	}

	/**
	 * Starts a new independent markup, as the instance is shared between them
	 */
	public function reset( int $depth = 0 ): void {
		$this->depth    = $depth;
		$this->is_first = true;
	}

	protected function print_break_before_node(): void {
		if ( ! $this->is_first ) {
			$this->formatter->line_start( $this->depth );
		}

		$this->is_first = false;
	}
}
