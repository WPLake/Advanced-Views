<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

interface Frontend_Library {
	// distinct name.
	public static function get_name(): string;

	public static function get_docs_url(): string;

	/**
	 * @return string[] without extension
	 */
	public static function get_css_file_names(): array;

	/**
	 * [subject => replace]
	 * Used e.g. to patch wrong url pointers - 'url(../'.
	 *
	 * @return array<string,string>
	 */
	public static function get_css_patches( Asset_Resolver $resolver ): array;

	/**
	 * @return string[] without extension
	 */
	public static function get_js_file_names(): array;
}
