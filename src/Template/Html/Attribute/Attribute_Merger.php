<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Attribute;

defined( 'ABSPATH' ) || exit;

use Closure;

final class Attribute_Merger {
	/**
	 * Attributes without a merger are replaced by the new value
	 *
	 * @param array<string,mixed> $previous
	 * @param array<string,mixed> $actual
	 *
	 * @return array<string,mixed>
	 */
	public static function merge( array $previous, array $actual ): array {
		$mergers = self::get_mergers();
		$merged  = $previous;

		foreach ( $actual as $name => $value ) {
			if ( key_exists( $name, $mergers ) &&
				key_exists( $name, $previous ) ) {
				$merger     = $mergers[ $name ];
				$prev_value = $previous[ $name ];

				$merged[ $name ] = $merger( $prev_value, $value );
			} else {
				$merged[ $name ] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Each side of a merge can be a string or an array
	 *
	 * @return array<string,Closure(mixed,mixed):mixed> attribute name => merger of the existing and the new value
	 */
	protected static function get_mergers(): array {
		return array(
			'class' => fn( $existing, $value ) => array_merge(
				self::to_classes( $existing ),
				self::to_classes( $value )
			),
			'style' => fn( $existing, $value ) => array_merge(
				self::to_styles( $existing ),
				self::to_styles( $value )
			),
		);
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed[]
	 */
	protected static function to_classes( $value ): array {
		if ( is_array( $value ) ) {
			return $value;
		}

		$text         = is_string( $value ) ? $value : '';
		$names        = explode( ' ', $text );
		$filled_names = array_filter(
			$names,
			static fn( string $name ): bool => strlen( $name ) > 0
		);

		return array_values( $filled_names );
	}

	/**
	 * A string is parsed from the 'property:value;' form
	 *
	 * @param mixed $value
	 *
	 * @return mixed[]
	 */
	protected static function to_styles( $value ): array {
		if ( is_array( $value ) ) {
			return $value;
		}

		$text         = is_string( $value ) ? $value : '';
		$declarations = array();
		$lines        = explode( ';', $text );

		foreach ( $lines as $line ) {
			$parts = explode( ':', $line, 2 );

			if ( 2 === count( $parts ) ) {
				$property = trim( $parts[0] );

				$declarations[ $property ] = trim( $parts[1] );
			}
		}

		return $declarations;
	}
}
