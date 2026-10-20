<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtended;

defined( 'ABSPATH' ) || exit;

final class Attribute_Renderer {
	/**
	 * 'class' and 'style' also accept arrays: a list of classes, a property => value map
	 *
	 * @param array<string,mixed> $attributes
	 */
	public static function print_attributes( array $attributes ): void {
		foreach ( $attributes as $name => $value ) {
			self::print_attribute( $name, $value );
		}
	}

	/**
	 * @param mixed $value
	 */
	protected static function print_attribute( string $name, $value ): void {
		$attribute_value = self::normalize_attribute_value( $name, $value );

		printf(
			' %s="%s"',
			esc_html( $name ),
			// phpcs:ignore WordPress.Security.EscapeOutput
			$attribute_value
		);
	}

	/**
	 * @param mixed $value
	 */
	protected static function normalize_attribute_value( string $name, $value ): string {
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
			'class' => fn( array $classes ) => self::join_classes( $classes ),
			'style' => fn( array $declarations ) => self::join_style_declarations( $declarations ),
		);
	}

	/**
	 * @param array<mixed> $classes
	 */
	protected static function join_classes( array $classes ): string {
		$escaped_classes = array_map( fn( $class ) => self::escape_value( $class ), $classes );

		return join( ' ', $escaped_classes );
	}

	/**
	 * @param array<mixed> $declarations
	 */
	protected static function join_style_declarations( array $declarations ): string {
		$lines = array();

		foreach ( $declarations as $property => $value ) {
			$lines[] = sprintf(
				'%s:%s;',
				esc_html( (string) $property ),
				self::escape_value( $value )
			);
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
