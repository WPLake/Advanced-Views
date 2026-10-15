<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS;

defined( 'ABSPATH' ) || exit;

interface JS_Library {
	/**
	 * Each item is either a file name without extension, or an external url.
	 * The enqueue order must be preserved.
	 *
	 * @return string[]
	 */
	public static function get_js_files( File_Context $context ): array;
}
