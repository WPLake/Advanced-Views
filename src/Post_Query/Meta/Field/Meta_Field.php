<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Meta\Field;

defined( 'ABSPATH' ) || exit;

use WP_Post;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

abstract class Meta_Field {
	const OBJECT_FIELD_TYPES        = array( 'relationship', 'post_object' );
	const NUMERIC_FIELD_TYPES       = array( 'number', 'range' );
	const NO_VALUE_COMPARISONS      = array( 'EXISTS', 'NOT EXISTS' );
	const LIST_FIELD_COMPARISON_MAP = array(
		'='  => 'LIKE',
		'!=' => 'NOT LIKE',
	);
	const LIST_VALUE_COMPARISON_MAP = array(
		'='  => 'IN',
		'!=' => 'NOT IN',
	);

	public static function is_object_field( string $field_type ): bool {
		return in_array( $field_type, self::OBJECT_FIELD_TYPES, true );
	}

	public static function is_numeric_field( string $field_type ): bool {
		return in_array( $field_type, self::NUMERIC_FIELD_TYPES, true );
	}

	public static function is_value_comparison( string $comparison ): bool {
		$is_no_value_comparison = in_array( $comparison, self::NO_VALUE_COMPARISONS, true );

		return ! $is_no_value_comparison;
	}

	/**
	 * Relationship & post_object (with multiple checkbox) can be found only in this manner.
	 * https://www.advancedcustomfields.com/resources/querying-relationship-fields/.
	 */
	public static function resolve_list_field_comparison( string $origin_comparison ): string {
		return self::resolve_comparison(
			$origin_comparison,
			self::LIST_FIELD_COMPARISON_MAP
		);
	}

	public static function resolve_list_value_comparison( string $origin_comparison ): string {
		return self::resolve_comparison(
			$origin_comparison,
			self::LIST_VALUE_COMPARISON_MAP
		);
	}

	/**
	 * @param mixed $value
	 */
	public static function stringify_value( $value ): string {
		if ( is_object( $value ) ) {
			return self::stringify_object_value( $value );
		}

		return string( $value );
	}

	/**
	 * @param array<string, string> $comparison_map
	 */
	protected static function resolve_comparison( string $origin_comparison, array $comparison_map ): string {
		if ( key_exists( $origin_comparison, $comparison_map ) ) {
			return $comparison_map[ $origin_comparison ];
		}

		return $origin_comparison;
	}

	protected static function stringify_object_value( object $object_value ): string {
		$transformers = self::get_object_transformers( $object_value );

		// Last matched transformer gets saved under key 1.
		$transformer = $transformers[1] ?? null;

		if ( is_callable( $transformer ) ) {
			return $transformer();
		}

		// unknown object type.
		return '';
	}

	/**
	 * @return array<int, callable():string>
	 */
	protected static function get_object_transformers( object $object_value ): array {
		return array(
			is_a( $object_value, WP_Post::class ) =>
				fn() => string( $object_value, 'ID' ),
		);
	}
}
