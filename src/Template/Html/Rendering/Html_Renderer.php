<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Html_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Html_Renderer {
	private ?Html_Formatter $formatter;

	protected Tag_Renderer $tag_renderer;
	protected Html_Stringifier $stringifier;

	public function __construct(
		Tag_Renderer $tag_renderer,
		Html_Stringifier $stringifier,
		?Html_Formatter $formatter = null
	) {
		$this->tag_renderer = $tag_renderer;
		$this->stringifier  = $stringifier;
		$this->formatter    = $formatter;

		$node_handlers = array(
			Html_Tag::class => fn( object $value ) => $value instanceof Html_Tag ?
				$this->render_tag( $value ) :
				'',
			Closure::class  => fn( object $value ) => $value instanceof Closure ?
				self::capture_output( $value ) :
				'',
		);

		$this->stringifier->add_object_handlers( $node_handlers );
	}

	public function set_formatter( ?Html_Formatter $formatter ): self {
		$this->formatter = $formatter;

		return $this;
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

			$node_html = $this->stringifier->stringify( $node );

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

	protected function render_tag( Html_Tag $tag ): string {
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

	protected static function capture_output( Closure $do_print ): string {
		ob_start();
		$do_print();

		return (string) ob_get_clean();
	}
}
