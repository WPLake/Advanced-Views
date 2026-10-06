<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Core\Settings;

defined( 'ABSPATH' ) || exit;

interface Query_Settings {
	/**
	 * @return string[]
	 */
	public function get_post_types(): array;

	/**
	 * @return string[]
	 */
	public function get_post_statuses(): array;

	public function is_ignore_sticky_posts(): bool;

	/**
	 * @return int[]
	 */
	public function get_post_in(): array;

	/**
	 * @return int[]
	 */
	public function get_post_not_in(): array;

	public function get_limit(): int;

	public function get_order(): string;

	public function get_order_by(): string;

	public function get_order_by_meta_field_source(): string;

	public function get_order_by_meta_acf_field_id(): string;

	public function get_tax_filter(): Tax_Filter;

	public function get_meta_filter(): Meta_Filter;

	public function is_with_pagination(): bool;

	public function get_pagination_per_page(): int;

	public function get_extra_query_arguments(): string;

	public function get_unique_id( bool $is_without_prefix = false ): string;
}
