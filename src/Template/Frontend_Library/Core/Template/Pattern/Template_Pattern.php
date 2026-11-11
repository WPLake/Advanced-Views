<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

/**
 * Static classes of a pattern are declared as is. The generated (BEM) classes are added by the Template_Builder.
 * Dynamic values are Variable_Token clones, extended with the needed item path.
 *
 * Which method produces which part of the markup (a single Image field, then a Gallery field):
 *
 * <div class="avf-layout__image">                       <- get_row_wrapper() (the row: label + field)
 *     <p class="avf-layout__image-label">...</p>        <- the label, outside the pattern's scope
 *     <div class="avf-layout__image-field"              <- get_field_wrappers()[0] (+ get_item_attributes() for
 *          data-src="{{ image.full_size }}">                a single value, e.g. data-src)
 *         <img class="avf-layout__image-image" .../>    <- the field's own element, outside the pattern's scope
 *     </div>
 * </div>
 *
 * <div class="avf-layout__gallery">                     <- get_row_wrapper()
 *     <div class="avf-layout__gallery-field">           <- get_field_wrappers()[0], merged into the generic field
 *         <ul class="avf-layout__gallery-list">         <- get_field_wrappers()[1+], the last one is the list
 *             <li class="avf-layout__gallery-item"      <- get_item_wrappers() (one per item)
 *                 data-full-size="...">                    + get_item_attributes() (on every item)
 *                 <img .../>                            <- the item's own element
 *             </li>
 *         </ul>
 *     </div>
 * </div>
 */
interface Template_Pattern {
	/**
	 * The wrapper of the whole row, i.e. the label together with the field. Null for none.
	 */
	public static function get_row_wrapper(): ?Html_Tag;

	/**
	 * Everything between the row and the items, from the outermost tag: nested, so the first one is the top parent.
	 * The first one is merged into the generic field wrapper (takes its name and attributes, gets the field
	 * classes), the last of the rest is the 'list'.
	 *
	 * @return Html_Tag[]
	 */
	public static function get_field_wrappers( Variable_Token $field ): array;

	/**
	 * The wrappers around every single item of the list (e.g. the 'li' of a gallery), the first one is the top parent.
	 *
	 * @return Html_Tag[]
	 */
	public static function get_item_wrappers( Variable_Token $item ): array;

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
