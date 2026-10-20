<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

defined( 'ABSPATH' ) || exit;

final class Attribute_Renderer {
	/**
	 * @param array<string,mixed> $attributes
	 */
	public static function render_attributes( array $attributes ): string {
		ob_start();
		self::print_attributes( $attributes );

		return (string) ob_get_clean();
	}

	/**
	 * 'class' and 'style' also accept arrays: a list of classes, a property => value map
	 *
	 * @param array<string,mixed> $attributes
	 */
	public static function print_attributes( array $attributes ): void {
		foreach ( $attributes as $name => $value ) {
			echo ' ';

			self::print_attribute( $name, $value );
		}
	}

	/**
	 * @param mixed $value
	 */
	protected static function print_attribute( string $name, $value ): void {
		$escaped_value = Attribute_Normalizer::safe_normalize( $name, $value );

		printf(
			'%s="%s"',
			esc_html( $name ),
			// phpcs:ignore WordPress.Security.EscapeOutput
			$escaped_value
		);
	}
}
