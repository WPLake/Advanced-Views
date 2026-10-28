<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Attribute;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

class Attribute_Renderer {
	protected Attribute_Normalizer $normalizer;
	protected Html_Escaper $escaper;
	protected bool $display_empty_attributes;

	public function __construct(
		Html_Escaper $escaper,
		?Attribute_Normalizer $normalizer = null
	) {
		$this->escaper                  = $escaper;
		$this->normalizer               = $normalizer ?? new Attribute_Normalizer( $escaper );
		$this->display_empty_attributes = false;
	}

	public function should_display_empty_attributes( bool $display_empty_attributes ): self {
		$this->display_empty_attributes = $display_empty_attributes;

		return $this;
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	public function print_attributes( array $attributes ): void {
		foreach ( $attributes as $name => $value ) {
			$this->print_attribute( $name, $value );
		}
	}

	/**
	 * @param mixed $value
	 */
	protected function print_attribute( string $name, $value ): void {
		if ( is_bool( $value ) ) {
			$this->print_boolean_attribute( $name, $value );

			return;
		}

		$normalized_value = $this->normalizer->normalize_attribute( $name, $value );
		$should_display   = $this->display_empty_attributes ||
							strlen( $normalized_value ) > 0;

		if ( $should_display ) {
			$this->print_pair( $name, $normalized_value );
		}
	}

	protected function print_boolean_attribute( string $name, bool $value ): void {
		if ( $value ) {
			$this->print_pair( $name, '' );
		}
	}

	protected function print_pair( string $name, string $escaped_value ): void {
		$escaped_key = $this->escaper->esc_html( $name );

		printf(
			' %s="%s"',
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$escaped_key,
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$escaped_value
		);
	}
}
