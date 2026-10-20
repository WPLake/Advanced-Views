<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtended;

defined( 'ABSPATH' ) || exit;

final class Attribute_Normalizer {
	/**
	 * Returns the escaped attribute value
	 *
	 * @param mixed $value
	 */
	public static function safe_normalize( string $name, $value ): string {
		$normalizers = self::attribute_normalizers();

		if ( is_array( $value ) ) {
			return key_exists( $name, $normalizers ) ?
				$normalizers[ $name ]( $value ) :
				'';
		}

		return self::escape_value( $value );
	}

	/**
	 * @return array<string,callable(array<mixed>):string>
	 */
	protected static function attribute_normalizers(): array {
		return array(
			'class' => fn( array $classes ) => self::normalize_class( $classes ),
			'style' => fn( array $declarations ) => self::normalize_style( $declarations ),
		);
	}

	/**
	 * @param array<mixed> $classes
	 */
	protected static function normalize_class( array $classes ): string {
		$escaped_classes = array_map(
			fn( $tag_class ) => self::escape_value( $tag_class ),
			$classes
		);

		return join( ' ', $escaped_classes );
	}

	/**
	 * @param array<mixed> $declarations
	 */
	protected static function normalize_style( array $declarations ): string {
		$lines = array();

		foreach ( $declarations as $property => $value ) {
			$property_name = string( $property );
			$style_value   = self::escape_value( $value );

			$lines[] = sprintf( '%s:%s;', esc_html( $property_name ), $style_value );
		}

		return join( '', $lines );
	}

	/**
	 * Objects (e.g. template tokens) are stringified as is, since their output is already a markup
	 *
	 * @param mixed $value
	 */
	protected static function escape_value( $value ): string {
		$string_value = stringExtended( $value );

		return is_object( $value ) ?
			$string_value :
			esc_html( $string_value );
	}
}
