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
				Html_Tag::class => fn( object $value ) => $value instanceof Html_Tag ?
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
		$parts         = array();
		$previous_node = null;

		foreach ( $nodes as $node ) {
			if ( ! is_null( $previous_node ) &&
				$this->formatter instanceof Html_Formatter ) {
				$parts[] = $this->formatter->format_interleave( $previous_node, $node );
			}

			$node_html = $this->render_node( $node );

			$parts[] = $this->formatter instanceof Html_Formatter ?
				$this->formatter->format_node( $node, $node_html ) :
				$node_html;

			$previous_node = $node;
		}

		return join( '', $parts );
	}

	/**
	 * @param Html_Node ...$nodes
	 */
	public function print_html( ...$nodes ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->render_html( ...$nodes );
	}

	/**
	 * @param Html_Node $node
	 */
	protected function render_node( $node ): string {
		if ( $node instanceof Html_Tag ) {
			return $this->render_tag_node( $node );
		}

		if ( $node instanceof Closure ) {
			ob_start();
			$node();

			return (string) ob_get_clean();
		}

		return $this->escaper->stringify( $node );
	}

	protected function render_tag_node( Html_Tag $tag ): string {
		$nodes     = $tag->get_nodes();
		$has_nodes = count( $nodes ) > 0;

		$content = $has_nodes ?
			$this->render_nested_nodes( $tag, $nodes ) :
			'';

		return $this->tag_renderer->render_tag( $tag, $content );
	}

	/**
	 * @param Html_Node $node
	 * @param Html_Node[] $nodes
	 */
	protected function render_nested_nodes( $node, array $nodes ): string {
		$render_nodes = fn(): string => $this->render_html( ...$nodes );

		return $this->formatter instanceof Html_Formatter ?
			$this->formatter->format_node_content( $node, $render_nodes ) :
			$render_nodes();
	}
}
