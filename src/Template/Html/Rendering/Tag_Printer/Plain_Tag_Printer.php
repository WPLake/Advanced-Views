<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag_Printer;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Escaper\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Normalizer\Attribute_Normalizer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Normalizer\Nested_Attribute_Normalizer;

final class Plain_Tag_Printer implements Tag_Printer {
	protected Attribute_Normalizer $normalizer;
	protected Html_Escaper $escaper;
	protected bool $display_empty_attributes;

	public function __construct(
		Html_Escaper $escaper,
		?Attribute_Normalizer $normalizer = null
	) {
		$this->escaper                  = $escaper;
		$this->normalizer               = $normalizer ?? new Nested_Attribute_Normalizer( $escaper );
		$this->display_empty_attributes = false;
	}

	public function should_display_empty_attributes( bool $display_empty_attributes ): self {
		$this->display_empty_attributes = $display_empty_attributes;

		return $this;
	}

	public function print_pair_tag( Html_Tag $tag, Closure $print_content ): void {
		$this->open_tag( $tag );
		echo '>';
		$print_content();
		$this->print_closing_tag( $tag );
	}

	public function print_single_tag( Html_Tag $tag ): void {
		$this->open_tag( $tag );
		echo '/>';
	}

	protected function open_tag( Html_Tag $tag ): void {
		$this->print_opening_tag( $tag );
		$this->print_attributes( $tag );
	}

	protected function print_opening_tag( Html_Tag $tag ): void {
		$tag_name    = $tag->get_tag();
		$escaped_tag = $this->escaper->esc_html( $tag_name );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<%s', $escaped_tag );
	}

	protected function print_closing_tag( Html_Tag $tag ): void {
		$tag_name    = $tag->get_tag();
		$escaped_tag = $this->escaper->esc_html( $tag_name );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '</%s>', $escaped_tag );
	}

	protected function print_attributes( Html_Tag $tag ): void {
		$tag_attributes = $tag->get_attributes();

		foreach ( $tag_attributes as $name => $value ) {
			$this->print_attribute( $name, $value );
		}
	}

	/**
	 * @param mixed $value
	 */
	protected function print_attribute( string $name, $value ): void {
		if ( is_bool( $value ) ) {
			$this->print_bool_attr( $name, $value );
		} else {
			$this->print_mixed_attr( $name, $value );
		}
	}

	protected function print_bool_attr( string $name, bool $value ): void {
		if ( $value ) {
			$this->print_attr_pair( $name, '' );
		}
	}

	/**
	 * @param mixed $value
	 */
	protected function print_mixed_attr( string $name, $value ): void {
		$normalized_value = $this->normalizer->normalize_attribute( $name, $value );
		$should_display   = $this->display_empty_attributes ||
							strlen( $normalized_value ) > 0;

		if ( $should_display ) {
			$this->print_attr_pair( $name, $normalized_value );
		}
	}

	protected function print_attr_pair( string $name, string $escaped_value ): void {
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
