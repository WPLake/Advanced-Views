<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

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

		$parts = array( $value );

		if ( is_array( $value ) ) {
			$parts = key_exists( $name, $normalizers ) ?
				$normalizers[ $name ]( $value ) :
				array();
		}

		$escaped_parts = array_map( fn( $part ) => self::escape_value( $part ), $parts );

		return join( '', $escaped_parts );
	}

	/**
	 * Every normalizer turns an array value into a flat list of parts (values and separators), to be escaped one by one
	 *
	 * @return array<string,callable(array<mixed>):array<mixed>>
	 */
	protected static function attribute_normalizers(): array {
		return array(
			'class' => fn( array $classes ) => self::get_class_parts( $classes ),
			'style' => fn( array $declarations ) => self::get_style_parts( $declarations ),
		);
	}

	/**
	 * @param array<mixed> $classes
	 *
	 * @return array<mixed>
	 */
	protected static function get_class_parts( array $classes ): array {
		$parts = array();

		foreach ( array_values( $classes ) as $index => $class ) {
			if ( $index > 0 ) {
				$parts[] = ' ';
			}

			$parts[] = $class;
		}

		return $parts;
	}

	/**
	 * @param array<mixed> $declarations
	 *
	 * @return array<mixed>
	 */
	protected static function get_style_parts( array $declarations ): array {
		$parts = array();

		foreach ( $declarations as $property => $value ) {
			array_push( $parts, $property, ':', $value, ';' );
		}

		return $parts;
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
