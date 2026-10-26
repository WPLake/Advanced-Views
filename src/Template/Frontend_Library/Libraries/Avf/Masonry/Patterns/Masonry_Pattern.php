<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Masonry_Pattern implements Frontend_Pattern, Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return 'masonry';
	}

	public static function get_label(): string {
		return __( 'Masonry', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array(
			tag( 'ul' ),
		);
	}

	public static function get_inner_wrappers(): array {
		return array(
			tag( 'li' )
				->attributes(
					array(
						'#data-width'  => Image_Field::WIDTH,
						'#data-height' => Image_Field::HEIGHT,
					)
				),
		);
	}

	public static function get_item_attributes(): array {
		return array();
	}

	public static function get_js_options(): array {
		return array(
			'ROW_MIN_HEIGHT' => 180,
			'GUTTER'         => 20,
			'MOBILE_GUTTER'  => 10,
			'MOBILE_WIDTH'   => 992,
		);
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return array(
			sprintf(
				'new AcfViewsMasonry(%s, %s)',
				esc_html( $variables->outer_wrapper ),
				esc_html( $variables->options )
			),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'list-style'           => 'none',
			'transition'           => 'opacity ease .1s',
			'::after'              => array(
				'content' => "''",
				'display' => 'block',
				'clear'   => 'both',
			),
			// hidden until the JS has laid the items out.
			':not([data-masonry])' => array(
				'opacity' => '0',
			),
			'img'                  => array(
				'display'    => 'block',
				'width'      => '100%',
				'height'     => '100%',
				'object-fit' => 'cover',
			),
		);
	}
}
