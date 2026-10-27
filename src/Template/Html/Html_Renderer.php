<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Tag_Printer\Tag_Printer;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Html_Renderer {
	protected Tag_Printer $tag_printer;
	/**
	 * @var string[]
	 */
	protected array $self_closing_tags;

	public function __construct( Tag_Printer $tag_printer ) {
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
	 * @param string[] $self_closing_tags
	 */
	public function set_self_closing_tags( array $self_closing_tags ): self {
		$this->self_closing_tags = $self_closing_tags;

		return $this;
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

				continue;
			}

			$this->tag_printer->print_node( $node );
		}
	}

	protected function print_tag( Html_Tag $tag ): void {
		if ( $this->is_self_closing( $tag ) ) {
			$this->tag_printer->print_single_tag( $tag );

			return;
		}

		$this->tag_printer->print_pair_tag(
			$tag,
			fn() => $this->print_html( ...$tag->get_content() )
		);
	}

	protected function is_self_closing( Html_Tag $tag ): bool {
		return in_array( $tag->get_tag(), $this->self_closing_tags, true );
	}
}
