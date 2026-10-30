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
	protected Tag_Renderer $tag_renderer;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper   = $escaper;
		$this->formatter = null;

		$this->tag_renderer = new Tag_Renderer( $escaper );

		$this->node_renderer = new Html_Stringifier( $escaper );
		$this->node_renderer->add_object_handlers(
			array(
				Html_Tag::class => fn( object $value ) =>$value instanceof Html_Tag ?
					$this->tag_renderer->render_tag( $value ) :
					'',
			)
		);
	}

	public function set_formatter( ?Html_Formatter $formatter ): self {
		$this->formatter = $formatter;

		return $this;
	}

	public function get_tag_renderer(): Tag_Renderer {
		return $this->tag_renderer;
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

		$content_renderer = $has_nodes ?
			$this->create_nested_node_renderer( $tag, $nodes ) :
			null;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return fn() => print( $this->tag_renderer->render_tag( $tag, $content_renderer ) );
	}

	/**
	 * @param Html_Node $node
	 * @param Html_Node[] $nodes
	 *
	 * @return Closure():string
	 */
	protected function create_nested_node_renderer( $node, array $nodes ): Closure {
		$printer = fn() => $this->print_html( ...$nodes );

		if ( $this->formatter instanceof Html_Formatter ) {
			return function () use ( $node, $printer ): string {
				ob_start();
				$this->formatter->print_node_content( $node, $printer );

				return (string) ob_get_clean();
			};
		}

		return fn(): string => $this->render_html( ...$nodes );
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
