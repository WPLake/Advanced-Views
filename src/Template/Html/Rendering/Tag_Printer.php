<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

use Org\Wplake\Advanced_Views\Template\Html\Attribute\Attribute_Normalizer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

/**
 * Prints the opening and closing parts of a tag, the content is up to Html_Renderer.
 */
final class Tag_Printer {
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

	public static function print_opening( Html_Tag $tag ): void {
		printf( '<%s', esc_html( $tag->get_tag() ) );
		self::print_attributes( $tag->get_attributes() );
		echo '>';
	}

	public static function print_closing( Html_Tag $tag ): void {
		if ( ! in_array( $tag->get_tag(), self::SELF_CLOSING_TAGS, true ) ) {
			printf( '</%s>', esc_html( $tag->get_tag() ) );
		}
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
