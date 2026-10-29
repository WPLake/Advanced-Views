<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Html_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Html_Renderer {
	public ?Html_Formatter $formatter;

	protected Html_Escaper $escaper;
	protected Tag_Printer $tag_printer;

	public function __construct( Html_Escaper $escaper, ?Tag_Printer $tag_printer = null ) {
		$this->escaper     = $escaper;
		$this->tag_printer = $tag_printer ?? new Tag_Printer( $escaper );
		$this->formatter   = null;
	}

	public function get_tag_printer(): Tag_Printer {
		return $this->tag_printer;
	}

	/**
	 * @param Html_Node ...$nodes
	 */
	public function render_html( ...$nodes ): string {
		ob_start();
		$this->print_html( ...$nodes );

		return (string) ob_get_clean();
	}

	/**
	 * @param Html_Node ...$nodes
	 */
	public function print_html( ...$nodes ): void {
		foreach ( $nodes as $index => $node ) {
			if ( $index > 0 && $this->formatter instanceof Html_Formatter ) {
				$this->formatter->print_interleave();
			}

			if ( $node instanceof Html_Tag ) {
				$this->print_tag( $node );
			} else {
				$this->print_node( $node );
			}
		}
	}

	protected function print_tag( Html_Tag $tag ): void {
		$nodes     = $tag->get_nodes();
		$has_nodes = count( $nodes ) > 0;

		$content_printer = $has_nodes ?
			$this->get_content_printer( $nodes ) :
			null;

		$this->tag_printer->print_tag( $tag, $content_printer );
	}

	/**
	 * @param Html_Node[] $nodes
	 *
	 * @return callback():mixed
	 */
	protected function get_content_printer( array $nodes ): callable {
		$printer = fn() => $this->print_html( ...$nodes );

		if ( $this->formatter instanceof Html_Formatter ) {
			return fn() => $this->formatter->print_content( $printer );
		}

		return $printer;
	}

	/**
	 * @param Html_Node $node
	 */
	protected function print_node( $node ): void {
		if ( $node instanceof Closure ) {
			$node();
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->escaper->stringify( $node );
		}
	}
}
