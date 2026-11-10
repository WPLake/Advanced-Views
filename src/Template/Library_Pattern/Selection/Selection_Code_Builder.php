<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Code_Piece_Printer;

defined( 'ABSPATH' ) || exit;

final class Selection_Code_Builder {
	/**
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	public static function build(
		Post_Selection_Settings $selection_settings,
		string $auto_discover_name,
		string $card_field_id,
		string $css_code,
		string $js_code
	): array {
		$code = array(
			'css' => array(),
			'js'  => array(),
		);

		$bem_name = $selection_settings->get_bem_name();
		$selector = sprintf( '.%s__%s', $bem_name, $card_field_id );

		if ( '' !== $css_code ) {
			ob_start();
			Code_Piece_Printer::print_code_piece( $auto_discover_name, $card_field_id, $css_code );
			$code['css'][ $card_field_id ] = (string) ob_get_clean();
		}

		if ( '' !== $js_code ) {
			ob_start();
			Code_Piece_Printer::print_js_code_piece( $auto_discover_name, $card_field_id, $js_code, $selector, false );
			$code['js'][ $card_field_id ] = (string) ob_get_clean();
		}

		return $code;
	}
}
