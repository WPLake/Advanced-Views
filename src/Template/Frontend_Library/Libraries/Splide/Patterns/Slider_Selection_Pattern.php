<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide\Patterns;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

/**
 * The markup of a Post Selection slider, its JS and CSS are in the Slider_Pattern.
 */
final class Slider_Selection_Pattern implements Template_Pattern {
	public static function get_wrapper(): Html_Tag {
		return tag( 'div' )
			->attributes( array( 'class' => 'splide' ) );
	}

	public static function get_outers( Variable_Token $field ): array {
		return array(
			tag( 'div' )
				->attributes( array( 'class' => 'splide__track' ) ),
			tag( 'div' )
				->attributes( array( 'class' => 'splide__list' ) ),
		);
	}

	public static function get_item_wrappers( Variable_Token $item ): array {
		return array();
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array(
			'class' => 'splide__slide',
		);
	}
}
