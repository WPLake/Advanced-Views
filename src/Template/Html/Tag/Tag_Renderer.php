<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Tag;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Attribute\Attribute_Normalizer;

defined( 'ABSPATH' ) || exit;

/**
 * @deprecated Use Tags_Renderer instead. The mark is a guard against direct usage: this class is only a helper of
 * Tags_Renderer.
 */
final class Tag_Renderer {
	protected const SELF_CLOSING_TAGS = array(
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

	/**
	 * 'class' and 'style' also accept arrays: a list of classes, a property => value map
	 *
	 * The content printer returns 'void|mixed': the 'mixed' is allowed to accept print callbacks, e.g.
	 * `fn() => print( $text )`. It's a Closure on purpose: strings and arrays are ambiguous with text and groups in
	 * Tags_Renderer, so other callables aren't supported.
	 *
	 * @deprecated Use Tags_Renderer::print_tags() instead. The mark is a guard against direct usage: this method is
	 * only a helper of Tags_Renderer.
	 *
	 * @param array<string,mixed> $attributes
	 * @param Closure():(void|mixed)|null $content_printer
	 */
	public static function print_tag(
		string $tag,
		array $attributes = array(),
		?Closure $content_printer = null
	): void {
		self::print_opening_tag( $tag, $attributes );

		if ( null !== $content_printer ) {
			$content_printer();
		}

		if ( ! self::is_self_closing( $tag ) ) {
			printf( '</%s>', esc_html( $tag ) );
		}
	}

	protected static function is_self_closing( string $tag ): bool {
		return in_array( $tag, self::SELF_CLOSING_TAGS, true );
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	protected static function print_opening_tag( string $tag, array $attributes ): void {
		printf( '<%s', esc_html( $tag ) );
		self::print_attributes( $attributes );
		echo '>';
	}

	/**
	 * 'class' and 'style' also accept arrays: a list of classes, a property => value map
	 *
	 * @param array<string,mixed> $attributes
	 */
	protected static function print_attributes( array $attributes ): void {
		$escaped_attributes = Attribute_Normalizer::safe_normalize( $attributes );

		foreach ( $escaped_attributes as $name => $escaped_value ) {
			echo ' ';

			printf(
				'%s="%s"',
				esc_html( $name ),
				// phpcs:ignore WordPress.Security.EscapeOutput
				$escaped_value
			);
		}
	}
}
