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
	private ?Html_Formatter $formatter;

	protected Html_Escaper $escaper;
	protected Html_Stringifier $node_renderer;
	protected Tag_Printer $tag_printer;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper   = $escaper;
		$this->formatter = null;

		$tag_printer = new Tag_Printer( $escaper );

		// fixme: use it below instead of manual resolving.
		$this->node_renderer = new Html_Stringifier( $escaper );
		$this->node_renderer->add_object_handlers(
			array(
				Html_Tag::class => fn( object $value ) =>$value instanceof Html_Tag ?
					// fixme make it return html.
					$tag_printer->print_tag( $value ) :
					'',
			)
		);
	}

	public function set_formatter( ?Html_Formatter $formatter ): self {
		$this->formatter = $formatter;

		return $this;
	}

	public function get_tag_renderer(): Tag_Printer {
		return $this->tag_printer;
	}

	public function get_node_renderer(): Html_Stringifier {
		return $this->node_renderer;
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
		$previous_node = null;

		foreach ( $nodes as $node ) {
			if ( ! is_null( $previous_node ) &&
				$this->formatter instanceof Html_Formatter ) {
				$this->formatter->print_interleave( $previous_node, $node );
			}

			$print_node = $node instanceof Html_Tag ?
				$this->create_tag_printer( $node ) :
				$this->create_node_printer( $node );

			if ( $this->formatter instanceof Html_Formatter ) {
				$this->formatter->print_node( $node, $print_node );
			} else {
				$print_node();
			}

			$previous_node = $node;
		}
	}

	/**
	 * @return callable():mixed
	 */
	protected function create_tag_printer( Html_Tag $tag ): callable {
		$nodes     = $tag->get_nodes();
		$has_nodes = count( $nodes ) > 0;

		$content_printer = $has_nodes ?
			$this->create_nested_node_printer( $tag, $nodes ) :
			null;

		return fn() => $this->tag_printer->print_tag( $tag, $content_printer );
	}

	/**
	 * @param Html_Node $node
	 * @param Html_Node[] $nodes
	 *
	 * @return callback():mixed
	 */
	protected function create_nested_node_printer( $node, array $nodes ): callable {
		$printer = fn() => $this->print_html( ...$nodes );

		if ( $this->formatter instanceof Html_Formatter ) {
			return fn() => $this->formatter->print_node_content( $node, $printer );
		}

		return $printer;
	}

	/**
	 * @param Html_Node $node
	 *
	 * @return callback():mixed
	 */
	protected function create_node_printer( $node ): callable {
		return fn()=> $this->print_node( $node );
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
