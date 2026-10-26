<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Variable_Token;

interface Template_Pattern {
	/**
	 * Flat items present the nested structure, so the first one is the top parent.
	 * Dynamic values are Variable_Token clones, extended with the needed item path.
	 *
	 * @return Html_Tag[]
	 */
	public static function get_outer_wrappers( Variable_Token $field ): array;

	/**
	 * Flat items present the nested structure, so the first one is the top parent.
	 * Dynamic values are Variable_Token clones, extended with the needed item path.
	 *
	 * @return Html_Tag[]
	 */
	public static function get_inner_wrappers( Variable_Token $item ): array;

	/**
	 * [ attributeName => value ]
	 *
	 * Applied to the end item: the element itself, or every item when the element is a list.
	 * A composite item (e.g. a row) takes them on its own root element.
	 *
	 * Dynamic values are clones of the item token with an item path of the item's field, so a pattern using them
	 * must be attached to an element whose items have that property (e.g. Gallery_Field, not any List_Field).
	 *
	 * @return array<string,string|Variable_Token>
	 */
	public static function get_item_attributes( Variable_Token $item ): array;
}
