<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

/**
 * Static classes of a pattern are declared as is. The generated (BEM) classes are added by the Template_Builder.
 * Dynamic values are Variable_Token clones, extended with the needed item path.
 */
interface Template_Pattern {
	/**
	 * The wrapper of the whole row (field with its label). Null for none.
	 */
	public static function get_row_wrapper(): ?Html_Tag;

	/**
	 * The wrapper of the field itself, it takes the generated field classes. Null for none.
	 */
	public static function get_field_wrapper( Variable_Token $field ): ?Html_Tag;

	/**
	 * Flat items present the nested structure, so the first one is the top parent.
	 * They are placed inside the field wrapper, the innermost one is the 'list'.
	 *
	 * @return Html_Tag[]
	 */
	public static function get_outer_wrappers( Variable_Token $field ): array;

	/**
	 * The wrappers of every item, the first one is the top parent.
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
	 * must be attached to an element whose items have that property (e.g. Gallery_Field_Type, not any List_Field_Type).
	 *
	 * @return array<string,string|Variable_Token>
	 */
	public static function get_item_attributes( Variable_Token $item ): array;

	/**
	 * True when the field label must be placed outside of the row (e.g. a slider wraps only the items).
	 */
	public static function is_label_out_of_row(): bool;

	/**
	 * A class for the items wrapper of a Post Selection. Empty string for none.
	 */
	public static function get_items_wrapper_class(): string;

	/**
	 * [ attributeName => value ]
	 *
	 * Added to the shortcode of a Post Selection.
	 *
	 * @return array<string,string>
	 */
	public static function get_shortcode_attributes(): array;
}
