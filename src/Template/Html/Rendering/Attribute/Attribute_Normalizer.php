<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Attribute;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

/**
 * @phpstan-type Normalizer_Callback callable(mixed):string
 */
final class Attribute_Normalizer {
	protected Html_Escaper $escaper;
	/**
	 * @var array<string,Normalizer_Callback>
	 */
	public array $normalizers;
	/**
	 * @var string[]
	 */
	public array $url_attributes;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper = $escaper;

		$this->url_attributes = array(
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
	 * @param mixed $value
	 */
	public function normalize_attribute( string $name, $value ): string {
		$normalizer = $this->resolve_normalizer( $name );

		if ( is_callable( $normalizer ) ) {
			return $normalizer( $value );
		}

		return $this->stringify( $name, $value );
	}

	/**
	 * @return Normalizer_Callback|null
	 */
	protected function resolve_normalizer( string $name ): ?callable {
		if ( key_exists( $name, $this->normalizers ) ) {
			return $this->normalizers[ $name ];
		}

		return null;
	}

	/**
	 * @param mixed $value
	 */
	protected function stringify( string $name, $value ): string {
		if ( is_string( $value ) &&
			$this->is_url( $name ) ) {
			return $this->escaper->esc_url( $value );
		}

		return $this->escaper->stringify( $value );
	}

	protected function is_url( string $name ): bool {
		return in_array( $name, $this->url_attributes, true );
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
