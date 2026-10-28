<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag\Formatted_Tag_Printer;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Web_Component_Printer {
	protected Html_Renderer $renderer;
	protected Formatted_Tag_Printer $tag_printer;
	protected Html_Formatter $formatter;

	public function __construct( Formatted_Tag_Printer $tag_printer, Html_Formatter $formatter, Html_Escaper $escaper ) {
		$this->tag_printer = $tag_printer;
		$this->formatter   = $formatter;
		$this->renderer    = new Html_Renderer( $escaper, $tag_printer );
	}

	/**
	 * Starts a new markup, with the indentation from scratch
	 */
	public function print_component( Html_Tag $component ): void {
		$this->start();

		$this->renderer->print_html( $component );
	}

	/**
	 * For the markup parts printed on their own, without a root tag
	 */
	public function start( int $depth = 0 ): self {
		$this->tag_printer->reset( $depth );

		return $this;
	}

	/**
	 * @param Html_Node ...$nodes
	 */
	public function print_html( ...$nodes ): void {
		$this->renderer->print_html( ...$nodes );
	}

	/**
	 * For the content of non-tag blocks (template conditions, loops), which must be indented like a tag content
	 */
	public function print_indented( Closure $print_content ): self {
		$this->tag_printer->print_indented( $print_content );

		return $this;
	}

	public function line_start(): self {
		$this->tag_printer->print_line_start();

		return $this;
	}

	public function tabs(): self {
		$this->tag_printer->print_tabs();

		return $this;
	}

	public function new_line( int $count = 1 ): self {
		$this->formatter->new_line( $count );

		return $this;
	}
}
