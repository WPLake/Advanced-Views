<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Css\Pattern;

defined( 'ABSPATH' ) || exit;

interface CSS_Pattern {
	/**
	 * [property => value] OR
	 * [innerSelector => [property => value]]
	 *
	 * An innerSelector is joined to the field selector with a space (descendant),
	 * except the colon-prefixed ones (e.g. '::after', ':not([data-masonry])'), which are joined without it.
	 * A combinator-prefixed one ('~ .item', '+ .item', '> .item') is joined with a space, so the combinator applies
	 * to the field itself.
	 *
	 * @return array<string, string|array<string, string>>
	 */
	public static function get_css_rules(): array;
}
