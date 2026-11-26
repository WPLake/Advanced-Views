<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Template;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Markup;

/**
 * Updates the template fields (markup, layout CSS) of a templated Selection.
 */
final class Template_Code_Updater {
	private Selection_Markup $post_selection_markup;

	public function __construct( Selection_Markup $post_selection_markup ) {
		$this->post_selection_markup = $post_selection_markup;
	}

	public function update_markup( Post_Selection_Settings $selection_settings ): void {
		ob_start();
		$this->post_selection_markup->print_markup( $selection_settings, false, true );

		$selection_settings->markup = (string) ob_get_clean();
	}

	public function update_layout_rules( Post_Selection_Settings $selection_settings ): void {
		$layout_css = $this->get_layout_css( $selection_settings );
		$is_filled  = strlen( $layout_css ) > 0;

		if ( $is_filled ) {
			$selection_settings->css_code = self::merge_layout_rules( $selection_settings->css_code, $layout_css );
		}
	}

	protected function get_layout_css( Post_Selection_Settings $selection_settings ): string {
		ob_start();
		$this->post_selection_markup->print_layout_css( $selection_settings );

		return (string) ob_get_clean();
	}

	protected static function merge_layout_rules( string $css_code, string $layout_css ): string {
		$has_rules = is_int( strpos( $css_code, '/*BEGIN LAYOUT_RULES*/' ) );

		if ( $has_rules ) {
			$replaced_css = preg_replace(
				'|\/\*BEGIN LAYOUT_RULES\*\/(.*\s)+\/\*END LAYOUT_RULES\*\/|',
				$layout_css,
				$css_code
			);

			return is_string( $replaced_css ) ?
				$replaced_css :
				$css_code;
		}

		return sprintf( "%s\n%s\n", $css_code, $layout_css );
	}
}
