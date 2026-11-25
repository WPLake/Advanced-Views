<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;

/**
 * Reads the typed values from the raw shortcode attributes, including the item arguments (posts, users...).
 */
final class Attribute_Reader {
	/**
	 * Item type => the shortcode argument with the item IDs.
	 */
	const ITEM_ARGUMENTS = array(
		Layout_Settings::ITEM_TYPE_POST    => 'posts',
		Layout_Settings::ITEM_TYPE_USER    => 'users',
		Layout_Settings::ITEM_TYPE_TERM    => 'terms',
		Layout_Settings::ITEM_TYPE_COMMENT => 'comments',
		Layout_Settings::ITEM_TYPE_MENU    => 'menus',
	);

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
	 */
	public static function get_int( array $attrs, string $name ): int {
		$value = $attrs[ $name ] ?? 0;

		return is_numeric( $value ) ?
			(int) $value :
			0;
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

	/**
	 * @param mixed $value Array, or a comma-separated string (the shortcode form)
	 *
	 * @return string[]
	 */
	public static function parse_list( $value ): array {
		$items        = self::split_value( $value );
		$trimmed      = array_map(
			fn( $item ): string => is_scalar( $item ) ? trim( (string) $item ) : '',
			$items
		);
		$filled_items = array_filter(
			$trimmed,
			fn( string $item ): bool => strlen( $item ) > 0
		);

		return array_values( $filled_items );
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed[]
	 */
	protected static function split_value( $value ): array {
		if ( is_string( $value ) ) {
			return explode( ',', $value );
		}

		return is_array( $value ) ?
			$value :
			array();
	}
}
