<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag;

use Org\Wplake\Advanced_Views\Template\Html\Attribute\Attribute_Normalizer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

final class Plain_Tag_Printer implements Tag_Printer {
	public function print_opening( Html_Tag $tag ): void {
		self::open_tag( $tag );
		echo '>';
	}

	public function print_closing( Html_Tag $tag ): void {
		$tag_name = $tag->get_tag();

		printf( '</%s>', esc_html( $tag_name ) );
	}

	public function print_void( Html_Tag $tag ): void {
		self::open_tag( $tag );
		echo '/>';
	}

	protected static function open_tag( Html_Tag $tag ): void {
		$tag_name       = $tag->get_tag();
		$tag_attributes = $tag->get_attributes();

		printf( '<%s', esc_html( $tag_name ) );
		self::print_attributes( $tag_attributes );
	}

	/**
	 * 'class' and 'style' also accept arrays: a list of classes, a property => value map
	 *
	 * @param array<string,mixed> $attributes
	 */
	protected static function print_attributes( array $attributes ): void {
		$escaped_attributes = Attribute_Normalizer::safe_normalize( $attributes );

		foreach ( $escaped_attributes as $name => $escaped_value ) {
			echo ' ';

			printf(
				'%s="%s"',
				esc_html( $name ),
				// phpcs:ignore WordPress.Security.EscapeOutput
				$escaped_value
			);
		}
	}
}
