<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide_Slider\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;

final class List_Slider implements Frontend_Pattern, Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return 'list_slider';
	}

	public static function get_label(): string {
		return __( 'Slider (list)', 'acf-views' );
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
				'tag'   => 'ul',
				'attrs' => array( 'class' => 'splide__list' ),
			),
		);
	}

	public static function get_inner_wrappers(): array {
		return array(
			array(
				'tag'   => 'li',
				'attrs' => array( 'class' => 'splide__slide' ),
			),
		);
	}

	public static function get_item_attributes(): array {
		return array();
	}

	public static function get_js_options(): array {
		return array(
			'type'    => 'loop',
			'perPage' => 1,
			'perMove' => 1,
		);
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return array(
			sprintf(
				'new Splide(%s, %s).mount()',
				esc_html( $variables->outer_wrapper ),
				esc_html( $variables->options )
			),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'ul' => array(
				'list-style' => 'none',
			),
		);
	}
}
