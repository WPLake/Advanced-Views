<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Escaping;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-type Escaper_Callback callable(mixed):string
 */
final class Attribute_Escaper {
	protected Html_Escaper $escaper;
	/**
	 * @var array<string,Escaper_Callback>
	 */
	private array $escapers;
	/**
	 * @var string[]
	 */
	private array $url_attributes;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper = $escaper;

		$this->url_attributes = array(
			'href',
			'src',
			'action',
			'formaction',
		);
		$this->escapers       = array(
			'class' => fn( $classes ) => is_array( $classes ) ?
				$this->escape_classes( $classes ) :
				$this->escaper->esc_attr( $classes ),
			'style' => fn( $declarations ) => is_array( $declarations ) ?
				$this->escape_styles( $declarations ) :
				$this->escaper->esc_attr( $declarations ),
		);
	}

	/**
	 * @param array<string,Escaper_Callback> $escapers
	 */
	public function set_escapers( array $escapers ): self {
		$this->escapers = $escapers;

		return $this;
	}

	/**
	 * @param array<string,Escaper_Callback> $normalizers
	 */
	public function add_normalizers( array $normalizers ): self {
		$this->escapers = array_merge( $this->escapers, $normalizers );

		return $this;
	}

	/**
	 * @param string[] $url_attributes
	 */
	public function set_url_attributes( array $url_attributes ): self {
		$this->url_attributes = $url_attributes;

		return $this;
	}

	/**
	 * @param string[] $url_attributes
	 */
	public function add_url_attributes( array $url_attributes ): self {
		$this->url_attributes = array_merge( $this->url_attributes, $url_attributes );

		return $this;
	}

	/**
	 * @param mixed $value
	 */
	public function esc_attr( string $name, $value ): string {
		$normalizer = $this->resolve_normalizer( $name );

		if ( is_callable( $normalizer ) ) {
			return $normalizer( $value );
		}

		return $this->escape( $name, $value );
	}

	/**
	 * @return Escaper_Callback|null
	 */
	protected function resolve_normalizer( string $name ): ?callable {
		if ( key_exists( $name, $this->escapers ) ) {
			return $this->escapers[ $name ];
		}

		return null;
	}

	/**
	 * @param mixed $value
	 */
	protected function escape( string $name, $value ): string {
		if ( is_string( $value ) &&
			$this->is_url( $name ) ) {
			return $this->escaper->esc_url( $value );
		}

		return $this->escaper->esc_attr( $value );
	}

	protected function is_url( string $name ): bool {
		return in_array( $name, $this->url_attributes, true );
	}

	/**
	 * @param mixed[] $classes
	 */
	protected function escape_classes( array $classes ): string {
		$class_names = array_map(
			fn( $class_item ) => $this->escaper->esc_attr( $class_item ),
			$classes
		);

		return join( ' ', $class_names );
	}

	/**
	 * @param mixed[] $declarations
	 */
	protected function escape_styles( array $declarations ): string {
		$lines = array();

		foreach ( $declarations as $property => $value ) {
			$escaped_name  = $this->escaper->esc_html( $property );
			$escaped_value = $this->escaper->esc_attr( $value );

			$lines[] = sprintf( '%s:%s;', $escaped_name, $escaped_value );
		}

		return join( '', $lines );
	}
}
