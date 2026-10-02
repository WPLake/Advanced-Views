<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Wraps a library code snippet into the auto-discoverable piece, and scopes JS to the target element(s).
 */
class Code_Piece_Printer {
	public static function print_css( Library $library, Target $target, string $piece_safe ): string {
		$auto_discover_name = $library->get_auto_discover_name();

		return self::wrap( $auto_discover_name, $target->field_id, $piece_safe );
	}

	public static function print_js( Library $library, Target $target, string $piece_safe ): string {
		$auto_discover_name = $library->get_auto_discover_name();
		$selector           = esc_html( $target->js_selector );
		$name               = esc_html( $target->field_id );

		$head = $target->is_in_repeater ?
			sprintf( "this.querySelectorAll('%s').forEach(item => {\n", $selector ) :
			sprintf( "var %1\$s = this.querySelector('%2\$s');\nif (%1\$s) {\n", $name, $selector );
		$tail = $target->is_in_repeater ?
			"\n});" :
			"\n}";

		return self::wrap( $auto_discover_name, $target->field_id, $head . $piece_safe . $tail );
	}

	protected static function wrap( string $auto_discover_name, string $name, string $piece_safe ): string {
		return sprintf(
			"\n\n/* %1\$s : %2\$s (auto-discover-begin) */\n\n%3\$s\n\n/* %1\$s : %2\$s (auto-discover-end) */\n\n",
			esc_html( $auto_discover_name ),
			esc_html( $name ),
			$piece_safe
		);
	}
}
