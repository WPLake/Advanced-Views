<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns;

defined( 'ABSPATH' ) || exit;

interface CSS_Pattern {
	/**
	 * [property => value] OR
	 * [innerSelector => [property => value]]
	 *
	 * An innerSelector is joined to the field selector with a space (descendant),
	 * except the colon-prefixed ones (e.g. '::after', ':not([data-masonry])'), which are joined without it.
	 *
	 * @return array<string, string|array<string, string>>
	 */
	public static function get_css_rules(): array;
}
