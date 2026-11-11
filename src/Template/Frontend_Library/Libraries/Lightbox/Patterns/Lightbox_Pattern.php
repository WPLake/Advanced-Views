<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Lightbox\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Lightbox_Pattern implements Style_Pattern, Template_Pattern {
	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			':hover'     => array(
				'cursor' => 'zoom-in',
			),
		);
	}

	public static function get_outers( Variable_Token $field ): array {
		return array(
			tag( 'div' ),
		);
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array(
			'data-full-size' => $item->clone()
				->add_item_path( 'full_size' ),
		);
	}

	public static function get_wrapper(): ?Html_Tag {
		return null;
	}

	/**
	 * @return Html_Tag[]
	 */
	public static function get_item_wrappers( Variable_Token $item ): array {
		return array();
	}
}
