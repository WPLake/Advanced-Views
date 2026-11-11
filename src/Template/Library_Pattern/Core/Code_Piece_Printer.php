<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

defined( 'ABSPATH' ) || exit;

final class Code_Piece_Printer {
	public static function print_js_code_piece(
		string $name,
		string $piece_safe,
		string $field_selector,
		bool $is_multiple
	): void {
		if ( $is_multiple ) {
			printf( "this.querySelectorAll('%s').forEach(item => {\n", esc_html( $field_selector ) );
		} else {
			printf(
				"var %s = this.querySelector('%s');\n",
				esc_html( $name ),
				esc_html( $field_selector )
			);
			printf( "if (%s) {\n", esc_html( $name ) );
		}

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $piece_safe;
		echo $is_multiple ?
			"\n});" :
			"\n}";
	}
}
