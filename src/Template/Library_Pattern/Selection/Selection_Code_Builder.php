<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Code_Piece_Printer;

defined( 'ABSPATH' ) || exit;

final class Selection_Code_Builder {
	public static function get_selector( Post_Selection_Settings $selection_settings, string $card_field_id ): string {
		$bem_name = $selection_settings->get_bem_name();

		return sprintf( '.%s__%s', $bem_name, $card_field_id );
	}

	/**
	 * @param string $js_snippet the finished JS snippet (already wrapped), or an empty string
	 *
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	public static function build(
		Post_Selection_Settings $selection_settings,
		string $auto_discover_name,
		string $card_field_id,
		string $css_code,
		string $js_snippet
	): array {
		$code = array(
			'css' => array(),
			'js'  => array(),
		);

		if ( '' !== $css_code ) {
			ob_start();
			Code_Piece_Printer::print_code_piece( $auto_discover_name, $card_field_id, $css_code );
			$code['css'][ $card_field_id ] = (string) ob_get_clean();
		}

		if ( '' !== $js_snippet ) {
			$code['js'][ $card_field_id ] = $js_snippet;
		}

		return $code;
	}
}
