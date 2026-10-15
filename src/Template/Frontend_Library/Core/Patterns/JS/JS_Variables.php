<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS;

defined( 'ABSPATH' ) || exit;

/**
 * Variable names.
 */
final class JS_Variables {
	/**
	 * Web component root (this/this.shadowRoot).
	 */
	public string $component = 'this';
	/**
	 * The very top field wrapper.
	 * - in some cases may be the same as the outer wrapper.
	 */
	public string $outer_wrapper = 'outer';
	/**
	 * Closest wrapper to the field value (whether value is a text or <img>).
	 * - in some cases may be the same as the outer wrapper.
	 */
	public string $inner_wrapper = 'inner';
	/**
	 * Name of the options variable.
	 */
	public string $options = 'options';
}
