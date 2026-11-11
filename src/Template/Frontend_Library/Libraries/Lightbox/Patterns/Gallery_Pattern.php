<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Lightbox\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern_Defaults;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Gallery_Pattern implements Style_Pattern, Template_Pattern {
	use Template_Pattern_Defaults;

	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			'img:hover'  => array(
				'cursor' => 'zoom-in',
			),
		);
	}

	public static function get_field_wrapper( Variable_Token $field ): Html_Tag {
		return tag( 'ul' );
	}

	public static function get_inner_wrappers( Variable_Token $item ): array {
		return array(
			tag( 'li' ),
		);
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array(
			'data-full-size' => $item->clone()
				->add_item_path( 'full_size' ),
		);
	}
}
