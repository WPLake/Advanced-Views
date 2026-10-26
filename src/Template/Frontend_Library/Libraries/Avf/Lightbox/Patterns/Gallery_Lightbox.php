<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Gallery_Lightbox implements Frontend_Pattern, Template_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return Image_Lightbox::get_id();
	}

	public static function get_label(): string {
		return Image_Lightbox::get_label();
	}

	public static function get_outer_wrappers( Variable_Token $field ): array {
		return array(
			tag( 'ul' ),
		);
	}

	public static function get_inner_wrappers( Variable_Token $item ): array {
		return array(
			tag( 'li' ),
		);
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return Image_Lightbox::get_item_attributes( $item );
	}

	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			'img:hover'  => array(
				'cursor' => 'zoom-in',
			),
		);
	}
}
