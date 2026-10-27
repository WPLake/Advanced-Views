<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Attributes;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtended;

final class Nested_Attribute_Normalizer implements Attribute_Normalizer {
	protected Html_Escaper $escaper;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper = $escaper;
	}

	public function normalize_attribute( string $name, $value ): string {
		if ( is_array( $value ) ) {
			$normalizers = self::get_normalizers();

			if ( key_exists( $name, $normalizers ) ) {
				$normalizer = $normalizers[ $name ];

				return $normalizer( $value );
			}
		}

		return $value;
	}

	/**
	 * @return array<string,callable(array<mixed>):string>
	 */
	protected static function get_normalizers(): array {
		return array(
			'class' => static fn( array $classes ) => self::normalize_class( $classes ),
			'style' => static fn( array $declarations ) => self::normalize_style( $declarations ),
		);
	}

	/**
	 * @param array<mixed> $classes
	 */
	protected static function normalize_class( array $classes ): string {
		$class_names = array_map(
			fn( $tag_class ) => stringExtended( $tag_class ),
			$classes
		);

		return join( ' ', $class_names );
	}

	/**
	 * @param array<mixed> $declarations
	 */
	protected static function normalize_style( array $declarations ): string {
		$lines = array();

		foreach ( $declarations as $property => $value ) {
			$property_name = string( $property );
			$style_value   = stringExtended( $value );

			$lines[] = sprintf( '%s:%s;', $property_name, $style_value );
		}

		return join( '', $lines );
	}
}
