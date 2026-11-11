<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;

defined( 'ABSPATH' ) || exit;

final class Selection_Code_Builder {
	public static function get_selector( Post_Selection_Settings $selection_settings, string $card_field_id ): string {
		$bem_name = $selection_settings->get_bem_name();

		return sprintf( '.%s__%s', $bem_name, $card_field_id );
	}
}
