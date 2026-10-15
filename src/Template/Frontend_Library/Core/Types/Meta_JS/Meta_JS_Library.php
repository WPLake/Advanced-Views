<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS\JS_Library;

interface Meta_JS_Library extends JS_Library {
	/**
	 * Meta of one library instance, collected per every place where the library is used.
	 *
	 * @return array<array-key, mixed>
	 */
	public static function get_js_instance_meta( Instance_Context $context ): array;

	/**
	 * [globalJsVarName => value]
	 *
	 * @param array<array-key, mixed> $instances the collected get_js_instance_meta() results
	 *
	 * @return array<string, mixed>
	 */
	public static function get_js_meta( array $instances ): array;
}
