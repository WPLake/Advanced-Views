<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns;

defined( 'ABSPATH' ) || exit;

interface CSS_Pattern {
	/**
	 * [property => value] OR
	 * [innerSelector => [property => value]]
	 *
	 * @return array<string, string|array<string, string>>
	 */
	public static function get_css_rules(): array;
}
