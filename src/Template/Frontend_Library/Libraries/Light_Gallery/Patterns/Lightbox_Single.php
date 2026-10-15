<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Elements\Image_Element;

defined( 'ABSPATH' ) || exit;

final class Lightbox_Single implements Frontend_Pattern, Template_Pattern, JS_Pattern, CSS_Pattern {
	const DATA_PROPERTIES = array(
		'#data-src'      => Image_Element::FULL_SIZE,
		'#data-sub-html' => Image_Element::CAPTION,
	);

	public static function get_id(): string {
		return 'lightbox';
	}

	public static function get_label(): string {
		return __( 'Lightbox', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array();
	}

	public static function get_inner_wrappers(): array {
		return array(
			'div' => self::DATA_PROPERTIES,
		);
	}

	public static function get_element_attributes(): array {
		return array();
	}

	public static function get_js_options(): array {
		return array(
			'selector'          => 'this',
			'closeOnTap'        => true,
			'download'          => false,
			'allowMediaOverlap' => false,
			'enableDrag'        => false,
			'counter'           => false,
		);
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return array(
			sprintf(
				'new lightGallery(%s, %s)',
				esc_html( $variables->inner_wrapper ),
				esc_html( $variables->options )
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
