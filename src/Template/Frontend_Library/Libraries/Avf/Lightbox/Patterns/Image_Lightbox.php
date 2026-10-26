<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Image_Lightbox implements Frontend_Pattern, Template_Pattern, CSS_Pattern {
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
		return array(
			tag( 'div' ),
		);
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array(
			'data-full-size' => $item
				->clone()
				->add_item_path( Image_Field::FULL_SIZE ),
		);
	}

	public static function get_css_rules(): array {
		return array(
			':hover' => array(
				'cursor' => 'zoom-in',
			),
		);
	}
}
