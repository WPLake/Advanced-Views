<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag\Tag_Printer;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Html_Renderer {
	protected const DEFAULT_SELF_CLOSING_TAGS = array(
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

	protected Tag_Printer $tag_printer;
	/**
	 * @var string[]
	 */
	protected array $self_closing_tags;

	public function __construct( Tag_Printer $tag_printer ) {
		$this->tag_printer       = $tag_printer;
		$this->self_closing_tags = self::DEFAULT_SELF_CLOSING_TAGS;
	}

	/**
	 * @param Html_Node ...$nodes
	 */
	public function render( ...$nodes ): string {
		ob_start();
		$this->print( ...$nodes );

		return (string) ob_get_clean();
	}

	/**
	 * @param Html_Node ...$nodes
	 */
	public function print( ...$nodes ): void {
		foreach ( $nodes as $node ) {
			$this->print_node( $node );
		}
	}

	/**
	 * @param string[] $self_closing_tags
	 */
	public function set_self_closing_tags( array $self_closing_tags ): self {
		$this->self_closing_tags = $self_closing_tags;

		return $this;
	}

	/**
	 * @param Html_Node $node
	 */
	protected function print_node( $node ): void {
		if ( $node instanceof Html_Tag ) {
			$this->print_tag( $node );

			return;
		}

		self::print_content( $node );
	}

	protected function print_tag( Html_Tag $tag ): void {
		if ( $this->is_self_closing( $tag ) ) {
			$this->tag_printer->print_void( $tag );

			return;
		}

		$this->tag_printer->print_opening( $tag );
		$this->print( ...$tag->get_content() );
		$this->tag_printer->print_closing( $tag );
	}

	protected function is_self_closing( Html_Tag $tag ): bool {
		return in_array( $tag->get_tag(), $this->self_closing_tags, true );
	}

	/**
	 * @param string|Htmlable|Closure $content
	 */
	protected static function print_content( $content ): void {
		if ( is_string( $content ) ) {
			echo esc_html( $content );

			return;
		}

		if ( $content instanceof Htmlable ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $content->to_html();

			return;
		}

		$content();
	}
}
