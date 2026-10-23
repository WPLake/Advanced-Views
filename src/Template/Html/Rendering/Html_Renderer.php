<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Html_Renderer {
	/**
	 * @deprecated Use the render_html() function directly (Template/Html/utils.php). The mark is a guard against
	 * direct usage: this method is only a helper of the function.
	 *
	 * @param Html_Node[] $nodes
	 */
	public static function render( array $nodes ): string {
		ob_start();
		self::print( $nodes );

		return (string) ob_get_clean();
	}

	/**
	 * @deprecated Use the print_html() function directly (Template/Html/utils.php). The mark is a guard against
	 * direct usage: this method is only a helper of the function.
	 *
	 * @param Html_Node[] $nodes
	 */
	public static function print( array $nodes ): void {
		foreach ( $nodes as $node ) {
			self::print_node( $node );
		}
	}

	/**
	 * @param Html_Node $node
	 */
	protected static function print_node( $node ): void {
		if ( $node instanceof Html_Tag ) {
			self::print_tag( $node );

			return;
		}

		self::print_content( $node );
	}

	protected static function print_tag( Html_Tag $tag ): void {
		$content = $tag->get_content();

		Tag_Printer::print_opening( $tag );
		self::print( $content );
		Tag_Printer::print_closing( $tag );
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
