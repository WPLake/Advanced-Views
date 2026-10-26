<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Tag_Printer;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Attributes\Attributes_Cleaner;
use Org\Wplake\Advanced_Views\Template\Html\Attributes\Attributes_Normalizer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

defined( 'ABSPATH' ) || exit;

final class Plain_Tag_Printer implements Tag_Printer {
	protected Attributes_Cleaner $cleaner;
	protected Html_Escaper $escaper;

	public function __construct( Html_Escaper $escaper, ?Attributes_Cleaner $cleaner = null ) {
		$this->escaper = $escaper;
		$this->cleaner = $cleaner ?? new Attributes_Normalizer( $escaper );
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
		if ( is_string( $node ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->escaper->esc_html( $node );

			return;
		}

		if ( $node instanceof Htmlable ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $node->to_html( $this->escaper );

			return;
		}

		$node();
	}

	protected function open_tag( Html_Tag $tag ): void {
		$tag_name       = $tag->get_tag();
		$tag_attributes = $tag->get_attributes();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<%s', $this->escaper->esc_html( $tag_name ) );
		$this->print_attributes( $tag_attributes );
	}

	/**
	 * 'class' and 'style' also accept arrays: a list of classes, a property => value map
	 *
	 * @param array<string,mixed> $attributes
	 */
	protected function print_attributes( array $attributes ): void {
		$escaped_attributes = $this->cleaner->clean_attributes( $attributes );

		foreach ( $escaped_attributes as $name => $escaped_value ) {
			echo ' ';

			printf(
				'%s="%s"',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$this->escaper->esc_html( $name ),
				// phpcs:ignore WordPress.Security.EscapeOutput
				$escaped_value
			);
		}
	}
}
