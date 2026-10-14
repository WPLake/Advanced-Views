<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;

defined( 'ABSPATH' ) || exit;

final class Lightbox_Multi implements Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_outer_wrappers(): array {
		return Inline_Gallery::get_outer_wrappers();
	}

	public static function get_inner_wrappers(): array {
		return Inline_Gallery::get_inner_wrappers();
	}

	public static function get_js_settings(): array {
		return array_merge(
			array(
				'counter'  => true,
				'#plugins' => array( 'window.lgThumbnail' ),
			),
			Lightbox_Single::get_js_settings(),
		);
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return Lightbox_Single::get_js_setup( $variables );
	}

	public static function get_css_rules(): array {
		return array_merge(
			array(
				'list-style' => 'none',
			),
			Lightbox_Single::get_css_rules()
		);
	}
}
