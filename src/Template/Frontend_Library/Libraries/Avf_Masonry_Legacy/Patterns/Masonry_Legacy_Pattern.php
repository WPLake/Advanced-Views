<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf_Masonry_Legacy\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf_Masonry\Patterns\Masonry_Pattern;

final class Masonry_Legacy_Pattern implements Frontend_Pattern, Template_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return Masonry_Pattern::get_id();
	}

	public static function get_label(): string {
		return Masonry_Pattern::get_label();
	}

	public static function get_outer_wrappers(): array {
		return Masonry_Pattern::get_outer_wrappers();
	}

	public static function get_inner_wrappers(): array {
		return Masonry_Pattern::get_inner_wrappers();
	}

	public static function get_item_attributes(): array {
		return Masonry_Pattern::get_item_attributes();
	}

	public static function get_css_rules(): array {
		return Masonry_Pattern::get_css_rules();
	}
}
