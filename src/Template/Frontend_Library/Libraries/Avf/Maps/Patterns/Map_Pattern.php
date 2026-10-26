<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Maps\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Map_Field_Base;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Map_Pattern implements Frontend_Pattern, Template_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return 'map';
	}

	public static function get_label(): string {
		return __( 'Map', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array(
			tag( 'div' )
				->attributes(
					array(
						'#data-zoom'       => Map_Field_Base::ZOOM,
						'#data-center-lat' => Map_Field_Base::CENTER_LAT,
						'#data-center-lng' => Map_Field_Base::CENTER_LNG,
					)
				),
		);
	}

	public static function get_inner_wrappers(): array {
		return array(
			tag( 'div' )
				->attributes(
					array(
						'#data-lat'   => Map_Field_Base::LAT,
						'#data-lng'   => Map_Field_Base::LNG,
						'#data-icon'  => Map_Field_Base::ICON,
						'#data-title' => Map_Field_Base::TITLE,
					)
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
