<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Html_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Raw_Html;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 * @phpstan-type Element_Renderer callable(Html_Element $element, string $content):string
 */
final class Html_Renderer {
	private Html_Escaper $escaper;
	private ?Html_Formatter $formatter;
	/**
	 * @var array<class-string, Element_Renderer>
	 */
	private array $element_renderers;

	public function __construct( Tag_Renderer $tag_renderer, Html_Escaper $escaper ) {
		$this->escaper           = $escaper;
		$this->formatter         = null;
		$this->element_renderers = array(
			Html_Tag::class => fn( $element, string $content ) => $element instanceof Html_Tag ?
				$tag_renderer->render_tag( $element, $content ) :
				'',
			Raw_Html::class => fn( $element ) => $element instanceof Raw_Html ?
				$element->get_html() :
				'',
		);
	}

	/**
	 * @param array<class-string, Element_Renderer> $renderers
	 */
	public function add_element_renderers( array $renderers ): self {
		$this->element_renderers = array_merge( $this->element_renderers, $renderers );

		return $this;
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

			$node_html = $node instanceof Html_Element ?
				$this->render_element( $node ) :
				$this->escaper->to_safe_string( $node );

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

	protected function render_element( Html_Element $element ): string {
		$renderer = $this->resolve_renderer( $element );

		if ( is_callable( $renderer ) ) {
			return $this->stringify_element( $element, $renderer );
		}

		return $this->escaper->to_safe_string( $element );
	}

	/**
	 * @return Element_Renderer|null
	 */
	protected function resolve_renderer( object $element ): ?callable {
		foreach ( $this->element_renderers as $type => $renderer ) {
			if ( is_a( $element, $type ) ) {
				return $renderer;
			}
		}

		return null;
	}

	/**
	 * @param Element_Renderer $renderer
	 */
	protected function stringify_element( Html_Element $element, callable $renderer ): string {
		$nodes     = $element->get_nodes();
		$has_nodes = count( $nodes ) > 0;

		$content = $has_nodes ?
			$this->render_nested_nodes( $element, $nodes ) :
			'';

		return $renderer( $element, $content );
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
