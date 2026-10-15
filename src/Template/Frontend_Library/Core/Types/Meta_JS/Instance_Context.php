<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS;

defined( 'ABSPATH' ) || exit;

interface Instance_Context {
	public function get_bem_name(): string;

	public function get_masonry_row_min_height(): int;

	public function get_masonry_gutter(): int;

	public function get_masonry_mobile_gutter(): int;

	public function get_item_selector(
		string $target,
		bool $is_inner_target = false,
		bool $is_skip_view = false
	): string;
}
