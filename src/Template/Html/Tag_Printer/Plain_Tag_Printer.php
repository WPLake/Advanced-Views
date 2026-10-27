<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Tag_Printer;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Attributes\Attribute_Normalizer;
use Org\Wplake\Advanced_Views\Template\Html\Attributes\Nested_Attribute_Normalizer;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

defined( 'ABSPATH' ) || exit;

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
		$tag_name = $tag->get_tag();

		$this->open_tag( $tag );
		echo '>';

		$print_content();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '</%s>', $this->escaper->esc_html( $tag_name ) );
	}

	public function print_single_tag( Html_Tag $tag ): void {
		$this->open_tag( $tag );
		echo '/>';
	}

	/**
	 * @param string|Htmlable|Closure $node
	 */
	public function print_node( $node ): void {
		if ( $node instanceof Closure ) {
			$node();
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->escaper->stringify( $node );
	}

	protected function open_tag( Html_Tag $tag ): void {
		$tag_name       = $tag->get_tag();
		$tag_attributes = $tag->get_attributes();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<%s', $this->escaper->esc_html( $tag_name ) );

		foreach ( $tag_attributes as $name => $value ) {
			$this->print_attribute( $name, $value );
		}
	}

	/**
	 * @param mixed $value
	 */
	protected function print_attribute( string $name, $value ): void {
		$normalized_value = $this->normalizer->normalize_attribute( $name, $value );
		$should_display   = $this->display_empty_attributes ||
							strlen( $normalized_value ) > 0;

		if ( $should_display ) {
			$escaped_key = $this->escaper->esc_html( $name );

			printf(
				' %s="%s"',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$escaped_key,
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$normalized_value
			);
		}
	}
}
