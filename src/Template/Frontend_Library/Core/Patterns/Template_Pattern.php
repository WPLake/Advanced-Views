<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns;

defined( 'ABSPATH' ) || exit;

interface Template_Pattern {
	/**
	 * [ tag => [attributeName => value] ]
	 * Flat items present the nested structure, so the first one is the top parent.
	 * Hash prefix in #attributeName means dynamic Markup_Element property.
	 *
	 * @return array<string, array<string,string>>
	 */
	public static function get_outer_wrappers(): array;

	/**
	 * [ tag => [attributeName => value] ]
	 * Flat items present the nested structure, so the first one is the top parent.
	 * Hash prefix in #attributeName means dynamic Markup_Element property.
	 *
	 * @return array<string, array<string,string>>
	 */
	public static function get_inner_wrappers(): array;

	/**
	 * [ attributeName => value ]
	 * Hash prefix in #attributeName means dynamic Markup_Element property.
	 *
	 * @return  array<string,string>
	 */
	public static function get_element_attributes(): array;
}
