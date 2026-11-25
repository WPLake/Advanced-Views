<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Attributes;

defined( 'ABSPATH' ) || exit;

/**
 * Reads the typed values from the raw shortcode attributes.
 */
final class Attribute_Reader {
	/**
	 * @param array<string,mixed> $attrs
	 */
	public static function get_string( array $attrs, string $name ): string {
		$value = $attrs[ $name ] ?? '';

		return is_string( $value ) ||
				is_numeric( $value ) ?
			(string) $value :
			'';
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return mixed[]
	 */
	public static function get_custom_arguments( array $attrs ): array {
		$custom_arguments = $attrs['custom-arguments'] ?? '';

		// can be an array, if called from Bridge.
		if ( is_string( $custom_arguments ) ) {
			return wp_parse_args( $custom_arguments );
		}

		return is_array( $custom_arguments ) ?
			$custom_arguments :
			array();
	}
}
