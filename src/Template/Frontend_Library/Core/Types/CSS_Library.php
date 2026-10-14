<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

interface CSS_Library {
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
}
