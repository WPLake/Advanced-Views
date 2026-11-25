<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;

/**
 * Translates the item args (posts, users...) of a typed Layout into the args the single-item detection reads.
 * Pure, so it's easy to test.
 */
final class Source_Args_Normalizer {
	const LEGACY_ARGS = array( 'object-id', 'post-slug', 'user-id', 'term-id', 'comment-id', 'menu-slug' );
	/**
	 * Item type => the shortcode arg with the item IDs.
	 */
	const ITEM_ARGS = array(
		Layout_Settings::ITEM_TYPE_POST    => 'posts',
		Layout_Settings::ITEM_TYPE_USER    => 'users',
		Layout_Settings::ITEM_TYPE_TERM    => 'terms',
		Layout_Settings::ITEM_TYPE_COMMENT => 'comments',
		Layout_Settings::ITEM_TYPE_MENU    => 'menus',
	);
	/**
	 * Item type => [ the legacy arg that takes the first ID, the legacy 'object-id' marker ('' means no marker) ].
	 */
	const SINGLE_ARGS = array(
		Layout_Settings::ITEM_TYPE_POST    => array( 'object-id', '' ),
		Layout_Settings::ITEM_TYPE_USER    => array( 'user-id', 'user' ),
		Layout_Settings::ITEM_TYPE_TERM    => array( 'term-id', 'term' ),
		Layout_Settings::ITEM_TYPE_COMMENT => array( 'comment-id', 'comment' ),
		Layout_Settings::ITEM_TYPE_MENU    => array( 'menu-slug', 'term' ),
	);

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return array<string,mixed>
	 */
	public static function normalize( string $item_type, array $attrs ): array {
		if ( Layout_Settings::ITEM_TYPE_CUSTOM === $item_type ) {
			return self::normalize_custom( $attrs );
		}

		if ( key_exists( $item_type, self::SINGLE_ARGS ) ) {
			return self::normalize_typed( $item_type, $attrs );
		}

		return $attrs;
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
	 * @param array<string,mixed> $attrs
	 *
	 * @return array<string,mixed>
	 */
	protected static function normalize_typed( string $item_type, array $attrs ): array {
		// typed Layouts ignore the legacy args, the item args are the only source.
		$attrs                       = array_diff_key( $attrs, array_flip( self::LEGACY_ARGS ) );
		list( $legacy_arg, $marker ) = self::SINGLE_ARGS[ $item_type ];

		$item_arg = self::ITEM_ARGS[ $item_type ];
		$ids      = self::parse_list( $attrs[ $item_arg ] ?? null );
		$first_id = $ids[0] ?? '';

		$has_marker   = strlen( $marker ) > 0;
		$has_first_id = strlen( $first_id ) > 0;

		if ( $has_marker ) {
			$attrs['object-id'] = $marker;
		}

		if ( $has_first_id ) {
			$attrs[ $legacy_arg ] = $first_id;
		}

		return $attrs;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return array<string,mixed>
	 */
	protected static function normalize_custom( array $attrs ): array {
		$options_pages = self::parse_list( $attrs['options-page'] ?? null );
		$options_page  = $options_pages[0] ?? '';
		$is_filled     = strlen( $options_page ) > 0;

		if ( $is_filled ) {
			$attrs['object-id'] = $options_page;
		}

		return $attrs;
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
