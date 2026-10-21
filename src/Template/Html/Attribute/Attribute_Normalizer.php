<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Attribute;

use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtended;

defined( 'ABSPATH' ) || exit;

final class Attribute_Normalizer {
	protected const URL_ATTRIBUTES = array(
		'href'       => true,
		'src'        => true,
		'action'     => true,
		'formaction' => true,
	);

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

		return self::escape_value( $name, $value );
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
			fn( $tag_class ) => self::escape_value( 'class', $tag_class ),
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
			$style_value   = self::escape_value( 'style', $value );

			$lines[] = sprintf( '%s:%s;', esc_html( $property_name ), $style_value );
		}

		return join( '', $lines );
	}

	/**
	 * Objects (e.g. template tokens) are stringified as is, since their output is already a markup
	 *
	 * @param mixed $value
	 */
	protected static function escape_value( string $attr_name, $value ): string {
		if ( $value instanceof Htmlable ) {
			return $value->to_html();
		}

		$string_value = stringExtended( $value );

		if ( key_exists( $attr_name, self::URL_ATTRIBUTES ) ) {
			return esc_url( $string_value );
		}

		return esc_html( $string_value );
	}
}
