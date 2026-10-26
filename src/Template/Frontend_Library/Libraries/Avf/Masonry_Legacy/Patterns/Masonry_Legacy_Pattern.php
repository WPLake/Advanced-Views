<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry_Legacy\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry\Patterns\Masonry_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Variable_Token;

final class Masonry_Legacy_Pattern implements Frontend_Pattern, Template_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return Masonry_Pattern::get_id();
	}

	public static function get_label(): string {
		return Masonry_Pattern::get_label();
	}

	public static function get_outer_wrappers( Variable_Token $field ): array {
		return Masonry_Pattern::get_outer_wrappers( $field );
	}

	public static function get_inner_wrappers( Variable_Token $item ): array {
		return Masonry_Pattern::get_inner_wrappers( $item );
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return Masonry_Pattern::get_item_attributes( $item );
	}

	public static function get_css_rules(): array {
		return Masonry_Pattern::get_css_rules();
	}
}
