<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide_Slider\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;

final class Div_Slider implements Frontend_Pattern, Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return 'div_slider';
	}

	public static function get_label(): string {
		return __( 'Slider (div)', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array(
			array(
				'tag'   => 'div',
				'attrs' => array( 'class' => 'splide' ),
			),
			array(
				'tag'   => 'div',
				'attrs' => array( 'class' => 'splide__track' ),
			),
			array(
				'tag'   => 'div',
				'attrs' => array( 'class' => 'splide__list' ),
			),
		);
	}

	public static function get_inner_wrappers(): array {
		return array();
	}

	public static function get_item_attributes(): array {
		return array(
			'class' => 'splide__slide',
		);
	}

	public static function get_js_options(): array {
		return List_Slider::get_js_options();
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return List_Slider::get_js_setup( $variables );
	}

	public static function get_css_rules(): array {
		return array();
	}
}
