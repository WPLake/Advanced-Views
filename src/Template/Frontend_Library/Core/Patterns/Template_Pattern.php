<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns;

defined( 'ABSPATH' ) || exit;

interface Template_Pattern {
	/**
	 * @return array<string, array<string,string>>
	 */
	public static function get_outer_wrappers(): array;

	/**
	 * @return array<string, array<string,string>>
	 */
	public static function get_inner_wrappers(): array;
}
