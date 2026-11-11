<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets;

defined( 'ABSPATH' ) || exit;

final class Code_Marker {
	public function wrap_code( string $auto_discover_name, string $name, string $code ): string {
		$discover_safe = esc_html( $auto_discover_name );
		$name_safe     = esc_html( $name );
		$new_lines     = "\n\n";

		$begin_marker = sprintf(
			'/* %s : %s (auto-discover-begin) */',
			$discover_safe,
			$name_safe
		);
		$end_marker   = sprintf(
			'/* %s : %s (auto-discover-end) */',
			$discover_safe,
			$name_safe
		);

		$parts = array(
			$new_lines,
			$begin_marker,
			$new_lines,
			$code,
			$new_lines,
			$end_marker,
			$new_lines,
		);

		return join( '', $parts );
	}

	/**
	 * @param array<string,string> $actual_pieces
	 */
	public function sync_code( string $code, array $actual_pieces ): string {
		// ungreedy, so that we can grep all the pieces.
		preg_match_all(
			'/\/\*([^*]*):([^*]+)\(auto-discover-begin\)[^*]+\*\/[\d\D]+\/\*[^*]*\(auto-discover-end\)[^*]+\*\//U',
			$code,
			$current_pieces,
			PREG_SET_ORDER
		);

		// 1. remove present pieces from the actual list (to avoid override)
		// 2. remove absent pieces from the code
		foreach ( $current_pieces as $current_piece ) {
			$type     = trim( $current_piece[1] );
			$name     = trim( $current_piece[2] );
			$piece_id = sprintf( '%s:%s', $type, $name );

			if ( key_exists( $piece_id, $actual_pieces ) ) {
				unset( $actual_pieces[ $piece_id ] );

				continue;
			}

			$code = str_replace(
				$current_piece[0],
				'',
				$code
			);
		}

		// remove empty lines (after removals).
		$code = trim( $code );

		// 3. add new pieces
		foreach ( $actual_pieces as $actual_piece ) {
			$code .= $actual_piece;
		}

		// remove empty lines (after additions).
		return trim( $code );
	}
}
