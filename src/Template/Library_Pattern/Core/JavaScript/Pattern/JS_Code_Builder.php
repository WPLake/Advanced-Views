<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Pattern;

defined( 'ABSPATH' ) || exit;

final class JS_Code_Builder {
	/**
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	public static function build( string $js_pattern, string $outer_wrapper ): string {
		$variables                = new JS_Variables();
		$variables->outer_wrapper = $outer_wrapper;

		$lines = array_merge(
			self::compose_options( $js_pattern::get_js_options(), $variables->options ),
			array_map(
				static fn( string $line ) => sprintf( "\t%s;", $line ),
				$js_pattern::get_js_setup( $variables )
			)
		);

		return implode( "\n", $lines );
	}

	/**
	 * @param array<string,mixed> $options
	 *
	 * @return string[]
	 */
	protected static function compose_options( array $options, string $variable ): array {
		if ( array() === $options ) {
			return array();
		}

		$lines = array( sprintf( "\tconst %s = {", esc_html( $variable ) ) );

		foreach ( $options as $name => $value ) {
			$is_literal = str_starts_with( $name, '#' );
			$name       = $is_literal ?
				substr( $name, 1 ) :
				$name;
			$value_js   = $is_literal ?
				esc_html( is_scalar( $value ) ? (string) $value : '' ) :
				(string) wp_json_encode( $value );

			$lines[] = sprintf( "\t\t%s: %s,", esc_html( $name ), $value_js );
		}

		$lines[] = "\t};";

		return $lines;
	}
}
