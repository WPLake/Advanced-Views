<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;

final class Lightbox_Multi implements Frontend_Pattern, Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return Lightbox_Single::get_id();
	}

	public static function get_label(): string {
		return Lightbox_Single::get_label();
	}

	public static function get_outer_wrappers(): array {
		return Inline_Gallery::get_outer_wrappers();
	}

	public static function get_inner_wrappers(): array {
		return Inline_Gallery::get_inner_wrappers();
	}

	public static function get_item_attributes(): array {
		return array();
	}

	public static function get_js_options(): array {
		return array(
			'closeOnTap'        => true,
			'download'          => false,
			'allowMediaOverlap' => false,
			'enableDrag'        => false,
			'counter'           => true,
			'#plugins'          => array( 'window.lgThumbnail' ),
		);
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return array(
			sprintf(
				'new lightGallery(%s, %s)',
				esc_html( $variables->outer_wrapper ),
				esc_html( $variables->options )
			),
		);
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
