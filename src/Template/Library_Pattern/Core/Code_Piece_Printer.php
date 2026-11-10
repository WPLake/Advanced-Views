<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

defined( 'ABSPATH' ) || exit;

final class Code_Piece_Printer {
	public static function print_code_piece( string $auto_discover_name, string $name, string $piece_safe ): void {
		echo "\n\n";
		printf( '/* %s : %s (auto-discover-begin) */', esc_html( $auto_discover_name ), esc_html( $name ) );
		echo "\n\n";
		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $piece_safe;
		echo "\n\n";
		printf( '/* %s : %s (auto-discover-end) */', esc_html( $auto_discover_name ), esc_html( $name ) );
		echo "\n\n";
	}

	public static function print_js_code_piece(
		string $auto_discover_name,
		string $name,
		string $piece_safe,
		string $field_selector,
		bool $is_multiple
	): void {
		ob_start();

		if ( $is_multiple ) {
			printf( "this.querySelectorAll('%s').forEach(item => {\n", esc_html( $field_selector ) );
		} else {
			printf( "var %s = this.querySelector('%s');\n", esc_html( $name ), esc_html( $field_selector ) );
			printf( "if (%s) {\n", esc_html( $name ) );
		}

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $piece_safe;
		echo $is_multiple ?
			"\n});" :
			"\n}";

		$js_code_safe = (string) ob_get_clean();

		self::print_code_piece( $auto_discover_name, $name, $js_code_safe );
	}
}
