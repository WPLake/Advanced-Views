<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS;

defined( 'ABSPATH' ) || exit;

interface JS_Pattern {
	/**
	 * [propertyName=> value]
	 *
	 * Hash prefix in #propertyName means value is a JS literal.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_js_options(): array;

	/**
	 * @return string[] code lines
	 */
	public static function get_js_setup( JS_Variables $variables ): array;
}
