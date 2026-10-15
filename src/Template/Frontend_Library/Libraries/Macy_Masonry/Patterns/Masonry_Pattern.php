<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Macy_Masonry\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;

final class Masonry_Pattern implements Frontend_Pattern, Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return 'masonry';
	}

	public static function get_label(): string {
		return __( 'Masonry', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array(
			array( 'tag' => 'ul' ),
		);
	}

	public static function get_inner_wrappers(): array {
		return array(
			array( 'tag' => 'li' ),
		);
	}

	public static function get_item_attributes(): array {
		return array();
	}

	public static function get_js_options(): array {
		return array(
			'columns' => 4,
			'margin'  => array(
				'x' => 0,
				'y' => 0,
			),
			'breakAt' => array(
				992 => 2,
				400 => 1,
			),
		);
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return array(
			sprintf(
				'%s = {...%1$s, container: %2$s}',
				esc_html( $variables->options ),
				esc_html( $variables->outer_wrapper )
			),
			'',
			sprintf( 'new Macy(%s)', esc_html( $variables->options ) ),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			'img'        => array(
				// block, as inline elements reserve some line-height spacing (even if line-height is 1).
				'display'   => 'block',
				'max-width' => '100%',
				'height'    => 'auto',
			),
		);
	}
}
