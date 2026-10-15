<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Maps\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Elements\Map_Element;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Elements\Map_Marker_Element;

final class Map_Pattern implements Frontend_Pattern, Template_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return 'map';
	}

	public static function get_label(): string {
		return __( 'Map', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array(
			array(
				'tag'   => 'div',
				'attrs' => array(
					'#data-zoom'       => Map_Element::ZOOM,
					'#data-center-lat' => Map_Element::CENTER_LAT,
					'#data-center-lng' => Map_Element::CENTER_LNG,
				),
			),
		);
	}

	public static function get_inner_wrappers(): array {
		return array(
			array(
				'tag'   => 'div',
				'attrs' => array(
					'#data-lat'   => Map_Marker_Element::LAT,
					'#data-lng'   => Map_Marker_Element::LNG,
					'#data-icon'  => Map_Marker_Element::ICON,
					'#data-title' => Map_Marker_Element::TITLE,
				),
			),
		);
	}

	public static function get_item_attributes(): array {
		return array();
	}

	public static function get_css_rules(): array {
		return array(
			'width'  => '100%',
			'height' => '400px',
		);
	}
}
