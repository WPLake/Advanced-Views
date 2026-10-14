<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types;

defined( 'ABSPATH' ) || exit;

interface JS_Library {
	/**
	 * @return string[] without extension
	 */
	public static function get_js_file_names(): array;

	/**
	 * External script urls to be enqueued after the library JS (.e.g. google maps script)
	 *
	 * @return string[]
	 */
	public static function get_js_extension_urls(): array;
}
