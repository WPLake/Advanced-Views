<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS;

defined( 'ABSPATH' ) || exit;

interface JS_Pattern {
	/**
	 * @return array<string, mixed>
	 */
	public static function get_js_settings(): array;

	/**
	 * @return string[]
	 */
	public static function get_js_setup( JS_Variables $variables ): array;
}
