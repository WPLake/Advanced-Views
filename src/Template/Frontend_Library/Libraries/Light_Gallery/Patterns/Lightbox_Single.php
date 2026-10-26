<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

final class Lightbox_Single implements Frontend_Pattern, Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return 'lightbox';
	}

	public static function get_label(): string {
		return __( 'Lightbox', 'acf-views' );
	}

	public static function get_outer_wrappers( Variable_Token $field ): array {
		return array();
	}

	public static function get_inner_wrappers( Variable_Token $item ): array {
		$data_properties = self::data_properties( $item );

		return array(
			tag( 'div' )
				->attributes( $data_properties ),
		);
	}

	/**
	 * @return array<string,Variable_Token>
	 */
	public static function data_properties( Variable_Token $item ): array {
		return array(
			'data-src'      => $item
				->clone()
				->add_item_path( Image_Field::FULL_SIZE ),
			'data-sub-html' => $item
				->clone()
				->add_item_path( Image_Field::CAPTION ),
		);
	}

	public static function get_item_attributes( Variable_Token $item ): array {
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
