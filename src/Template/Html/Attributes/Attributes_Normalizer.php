<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Attributes;

use Org\Wplake\Advanced_Views\Template\Html\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtended;

defined( 'ABSPATH' ) || exit;

final class Attributes_Normalizer implements Attributes_Cleaner {
	protected const URL_ATTRIBUTES = array(
		'href'       => true,
		'src'        => true,
		'action'     => true,
		'formaction' => true,
	);

	protected Html_Escaper $escaper;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper = $escaper;
	}

	public function clean_attributes( array $attributes ): array {
		$normalizers = $this->attribute_normalizers();
		$normalized  = array();

		foreach ( $attributes as $name => $value ) {
			if ( is_array( $value ) ) {
				if ( key_exists( $name, $normalizers ) ) {
					$normalizer          = $normalizers[ $name ];
					$normalized[ $name ] = $normalizer( $value );
				}

				continue;
			}

			$normalized[ $name ] = $this->escape_value( $name, $value );
		}

		return $normalized;
	}

	/**
	 * @return array<string,callable(array<mixed>):string>
	 */
	protected function attribute_normalizers(): array {
		return array(
			'class' => fn( array $classes ) => $this->normalize_class( $classes ),
			'style' => fn( array $declarations ) => $this->normalize_style( $declarations ),
		);
	}

	/**
	 * @param array<mixed> $classes
	 */
	protected function normalize_class( array $classes ): string {
		$escaped_classes = array_map(
			fn( $tag_class ) => $this->escape_value( 'class', $tag_class ),
			$classes
		);

		return join( ' ', $escaped_classes );
	}

	/**
	 * @param array<mixed> $declarations
	 */
	protected function normalize_style( array $declarations ): string {
		$lines = array();

		foreach ( $declarations as $property => $value ) {
			$property_name = string( $property );
			$style_value   = $this->escape_value( 'style', $value );

			$lines[] = sprintf( '%s:%s;', $this->escaper->esc_html( $property_name ), $style_value );
		}

		return join( '', $lines );
	}

	/**
	 * Objects (e.g. template tokens) are stringified as is, since their output is already a markup
	 *
	 * @param mixed $value
	 */
	protected function escape_value( string $attr_name, $value ): string {
		if ( $value instanceof Htmlable ) {
			return $value->to_html( $this->escaper );
		}

		$string_value = stringExtended( $value );

		if ( key_exists( $attr_name, self::URL_ATTRIBUTES ) ) {
			return $this->escaper->esc_url( $string_value );
		}

		return $this->escaper->esc_html( $string_value );
	}
}
