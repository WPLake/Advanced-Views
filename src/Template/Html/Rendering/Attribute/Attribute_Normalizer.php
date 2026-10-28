<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Attribute;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

/**
 * @phpstan-type Attribute_Normalizer callable(mixed):string
 */
final class Attribute_Normalizer {
	protected Html_Escaper $escaper;
	/**
	 * @var array<string,Attribute_Normalizer>
	 */
	protected array $normalizers;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper = $escaper;

		$this->url_attributes = $url_attributes ?? array(
			'href',
			'src',
			'action',
			'formaction',
		);
		$this->normalizers    = array(
			'class' => fn( $classes ) => is_array( $classes ) ?
				$this->normalize_class( $classes ) :
				$this->escaper->stringify( $classes ),
			'style' => fn( $declarations ) => is_array( $declarations ) ?
				$this->normalize_style( $declarations ) :
				$this->escaper->stringify( $declarations ),
		);
	}

	/**
	 * @param array<string,Attribute_Normalizer> $normalizers
	 */
	public function set_normalizers( array $normalizers ): self {
		$this->normalizers = $normalizers;

		return $this;
	}

	/**
	 * @param Attribute_Normalizer $normalizer
	 */
	public function set_normalizer( string $attribute, callable $normalizer ): self {
		$this->normalizers[ $attribute ] = $normalizer;

		return $this;
	}

	/**
	 * @param mixed $value
	 */
	public function normalize_attribute( string $name, $value ): string {
		if ( key_exists( $name, $this->normalizers ) ) {
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
