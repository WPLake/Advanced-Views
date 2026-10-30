<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Attribute_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

final class Attribute_Renderer {
	protected Attribute_Escaper $attribute_escaper;
	protected Html_Escaper $escaper;
	protected bool $display_empty;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper           = $escaper;
		$this->attribute_escaper = new Attribute_Escaper( $escaper );
		$this->display_empty     = false;
	}

	public function should_display_empty( bool $display_empty ): self {
		$this->display_empty = $display_empty;

		return $this;
	}

	public function get_escaper(): Attribute_Escaper {
		return $this->attribute_escaper;
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	public function render_attributes( array $attributes ): string {
		$html = '';

		foreach ( $attributes as $name => $value ) {
			$html .= $this->render_attribute( $name, $value );
		}

		return $html;
	}

	/**
	 * @param mixed $value
	 */
	protected function render_attribute( string $name, $value ): string {
		return is_bool( $value ) ?
			$this->render_bool_attribute( $name, $value ) :
			$this->render_mixed_attribute( $name, $value );
	}

	protected function render_bool_attribute( string $name, bool $value ): string {
		return $value ?
			$this->render_pair( $name, '' ) :
			'';
	}

	/**
	 * @param mixed $value
	 */
	protected function render_mixed_attribute( string $name, $value ): string {
		$normalized_value = $this->attribute_escaper->esc_attr( $name, $value );
		$should_display   = $this->display_empty ||
							strlen( $normalized_value ) > 0;

		return $should_display ?
			$this->render_pair( $name, $normalized_value ) :
			'';
	}

	protected function render_pair( string $name, string $escaped_value ): string {
		$escaped_key = $this->escaper->esc_html( $name );

		return sprintf( ' %s="%s"', $escaped_key, $escaped_value );
	}
}
