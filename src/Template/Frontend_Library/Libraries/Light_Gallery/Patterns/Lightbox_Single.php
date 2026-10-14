<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;

defined( 'ABSPATH' ) || exit;

final class Lightbox_Single implements JS_Pattern, CSS_Pattern {
	public static function get_js_settings(): array {
		return array(
			'selector'          => 'this',
			'closeOnTap'        => true,
			'download'          => false,
			'allowMediaOverlap' => false,
			'enableDrag'        => false,
			'counter'           => false,
		);
	}

	public static function get_js_literal_settings(): array {
		return array();
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return array(
			sprintf(
				'new lightGallery(%s, %s)',
				esc_html( $variables->field ),
				esc_html( $variables->settings )
			),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'img:hover' => array(
				'cursor' => 'zoom-in',
			),
		);
	}
}
