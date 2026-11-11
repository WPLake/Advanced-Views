<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

/**
 * Empty answers for a Template_Pattern, so a pattern declares only the parts it has.
 */
trait Template_Pattern_Defaults {
	public static function get_row_wrapper(): ?Html_Tag {
		return null;
	}

	public static function get_field_wrapper( Variable_Token $field ): ?Html_Tag {
		return null;
	}

	public static function get_outer_wrappers( Variable_Token $field ): array {
		return array();
	}

	public static function get_inner_wrappers( Variable_Token $item ): array {
		return array();
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array();
	}

	public static function is_label_out_of_row(): bool {
		return false;
	}

	public static function get_items_wrapper_class(): string {
		return '';
	}

	public static function get_shortcode_attributes(): array {
		return array();
	}
}
