<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Normalizer;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Rendering\Escaper\Html_Escaper;

/**
 * @phpstan-type Normalizer callable(mixed[]):string
 */
final class Nested_Attribute_Normalizer implements Attribute_Normalizer {
	protected Html_Escaper $escaper;
	/**
	 * @var array<string,Normalizer>
	 */
	protected array $normalizers;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper     = $escaper;
		$this->normalizers = array(
			'class' => fn( array $classes ) => $this->normalize_class( $classes ),
			'style' => fn( array $declarations ) => $this->normalize_style( $declarations ),
		);
	}

	/**
	 * @param array<string,Normalizer> $normalizers
	 */
	public function set_normalizers( array $normalizers ): self {
		$this->normalizers = $normalizers;

		return $this;
	}

	public function normalize_attribute( string $name, $value ): string {
		if ( is_array( $value ) &&
			key_exists( $name, $this->normalizers ) ) {
			$normalizer = $this->normalizers[ $name ];

			return $normalizer( $value );
		}

		return $this->escaper->stringify( $value );
	}

	/**
	 * @param mixed[] $classes
	 */
	protected function normalize_class( array $classes ): string {
		$class_names = array_map(
			fn( $tag_class ) => $this->escaper->stringify( $tag_class ),
			$classes
		);

		return join( ' ', $class_names );
	}

	/**
	 * @param mixed[] $declarations
	 */
	protected function normalize_style( array $declarations ): string {
		$lines = array();

		foreach ( $declarations as $property => $value ) {
			$escaped_name  = $this->escaper->esc_html( $property );
			$escaped_value = $this->escaper->stringify( $value );

			$lines[] = sprintf( '%s:%s;', $escaped_name, $escaped_value );
		}

		return join( '', $lines );
	}
}
