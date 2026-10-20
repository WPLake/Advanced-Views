<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

defined( 'ABSPATH' ) || exit;

abstract class Tag_Renderer {
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
	 * @param array<string,mixed> $attributes
	 * @param callable():void|null $content_printer
	 */
	public static function print_tag(
		string $tag,
		array $attributes = array(),
		?callable $content_printer = null
	): void {
		static::print_opening_tag( $tag, $attributes );

		if ( is_callable( $content_printer ) ) {
			$content_printer();
		}

		if ( ! static::is_self_closing( $tag ) ) {
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

		Attribute_Renderer::print_attributes( $attributes );

		echo '>';
	}
}
