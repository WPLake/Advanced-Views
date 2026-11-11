<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

/**
 * Static classes are declared as is, the generated (BEM) ones are added by the Template_Builder.
 * Dynamic values are Variable_Token clones, extended with the needed item path.
 */
interface Template_Pattern {
	public static function get_wrapper(): ?Html_Tag;

	/**
	 * The first tag is merged into the generic field wrapper, the last of the rest is the 'list'.
	 *
	 * @return Html_Tag[]
	 */
	public static function get_outers( Variable_Token $field ): array;

	/**
	 * @return Html_Tag[]
	 */
	public static function get_item_wrappers( Variable_Token $item ): array;

	/**
	 * Dynamic values are clones of the item token with an item path of the item's field, so a pattern using them
	 * must be attached to an element whose items have that property (e.g. Gallery_Field_Type, not any List_Field_Type).
	 *
	 * @return array<string,string|Variable_Token>
	 */
	public static function get_item_attributes( Variable_Token $item ): array;
}
