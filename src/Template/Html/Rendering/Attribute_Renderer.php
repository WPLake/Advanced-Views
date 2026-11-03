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
		$escaped_attributes = $this->normalize_attributes( $attributes );

		$html = '';

		foreach ( $escaped_attributes as $escaped_name => $escaped_value ) {
			$html .= $this->render_pair( $escaped_name, $escaped_value );
		}

		return $html;
	}

	/**
	 * @param array<string,mixed> $attributes
	 *
	 * @return array<string,string> escaped name => escaped value, only the attributes to display
	 */
	protected function normalize_attributes( array $attributes ): array {
		$escaped_attributes = array();

		foreach ( $attributes as $name => $value ) {
			$escaped_value = is_bool( $value ) ?
				self::normalize_bool( $value ) :
				$this->normalize_mixed( $name, $value );

			if ( is_string( $escaped_value ) ) {
				$escaped_name                        = $this->escaper->esc_html( $name );
				$escaped_attributes[ $escaped_name ] = $escaped_value;
			}
		}

		return $escaped_attributes;
	}

	/**
	 * @param mixed $value
	 */
	protected function normalize_mixed( string $name, $value ): ?string {
		$escaped_value = $this->attribute_escaper->esc_attr( $name, $value );
		$is_filled     = strlen( $escaped_value ) > 0;

		return $is_filled || $this->display_empty ?
			$escaped_value :
			null;
	}

	protected function render_pair( string $escaped_name, string $escaped_value ): string {
		return sprintf( ' %s="%s"', $escaped_name, $escaped_value );
	}

	protected static function normalize_bool( bool $value ): ?string {
		return $value ?
			'' :
			null;
	}
}
