<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Template;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Post_Selection_Markup;

/**
 * Updates the template fields (markup, layout CSS) of a templated Selection.
 */
final class Template_Code_Updater {
	private Post_Selection_Markup $post_selection_markup;

	public function __construct( Post_Selection_Markup $post_selection_markup ) {
		$this->post_selection_markup = $post_selection_markup;
	}

	public function update_markup( Post_Selection_Settings $selection_settings ): void {
		ob_start();
		$this->post_selection_markup->print_markup( $selection_settings, false, true );

		$selection_settings->markup = (string) ob_get_clean();
	}

	public function update_layout_rules( Post_Selection_Settings $selection_settings ): void {
		ob_start();
		$this->post_selection_markup->print_layout_css( $selection_settings );
		$layout_css = (string) ob_get_clean();

		if ( '' === $layout_css ) {
			return;
		}

		if ( false === strpos( $selection_settings->css_code, '/*BEGIN LAYOUT_RULES*/' ) ) {
			$selection_settings->css_code .= "\n" . $layout_css . "\n";

			return;
		}

		$css_code = preg_replace(
			'|\/\*BEGIN LAYOUT_RULES\*\/(.*\s)+\/\*END LAYOUT_RULES\*\/|',
			$layout_css,
			$selection_settings->css_code
		);

		if ( null === $css_code ) {
			return;
		}

		$selection_settings->css_code = $css_code;
	}
}
