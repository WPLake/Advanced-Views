<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag\Tag_Printer;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Html_Renderer {
	protected Html_Escaper $escaper;
	protected Tag_Printer $tag_printer;
	/**
	 * @var string[]
	 */
	public array $self_closing_tags;

	public function __construct( Html_Escaper $escaper, Tag_Printer $tag_printer ) {
		$this->escaper           = $escaper;
		$this->tag_printer       = $tag_printer;
		$this->self_closing_tags = array(
			'area',
			'base',
			'br',
			'col',
			'embed',
			'hr',
			'img',
			'input',
			'link',
			'meta',
			'source',
			'track',
			'wbr',
		);
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
		foreach ( $nodes as $node ) {
			if ( $node instanceof Html_Tag ) {
				$this->print_tag( $node );
			} else {
				$this->print_node( $node );
			}
		}
	}

	protected function print_tag( Html_Tag $tag ): void {
		if ( $this->is_self_closing( $tag ) ) {
			$this->tag_printer->print_single_tag( $tag );
		} else {
			$this->tag_printer->print_pair_tag(
				$tag,
				fn() => $this->print_html( ...$tag->get_content() )
			);
		}
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

	protected function is_self_closing( Html_Tag $tag ): bool {
		return in_array( $tag->get_tag(), $this->self_closing_tags, true );
	}
}
