<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns;

defined( 'ABSPATH' ) || exit;

interface Template_Pattern {
	/**
	 * [ [tag => string, attrs? => [attributeName => value]] ]
	 *
	 * Flat items present the nested structure, so the first one is the top parent.
	 * Hash prefix in #attributeName means dynamic Field_Value property.
	 *
	 * @return array<int, array{tag:string,attrs?:array<string,string>}>
	 */
	public static function get_outer_wrappers(): array;

	/**
	 * [ [tag => string, attrs? => [attributeName => value]] ]
	 *
	 * Flat items present the nested structure, so the first one is the top parent.
	 * Hash prefix in #attributeName means dynamic Field_Value property.
	 *
	 * @return array<int, array{tag:string,attrs?:array<string,string>}>
	 */
	public static function get_inner_wrappers(): array;

	/**
	 * [ attributeName => value ]
	 *
	 * Applied to the end item: the element itself, or every item when the element is a list.
	 * A composite item (e.g. a row) takes them on its own root element.
	 *
	 * Hash prefix in #attributeName means dynamic property of the item's Field_Value, so a pattern using it
	 * must be attached to an element whose items have that property (e.g. Gallery_Value, not any List_Value).
	 *
	 * @return  array<string,string>
	 */
	public static function get_item_attributes(): array;
}
