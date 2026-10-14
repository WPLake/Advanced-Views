<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Core\Settings;

defined( 'ABSPATH' ) || exit;

interface Term_Settings {
	public function get_taxonomy(): string;

	public function get_comparison(): string;

	public function get_dynamic_term(): string;

	public function get_custom_argument_name(): string;

	public function is_dynamic_term(): bool;

	public function get_term_id(): int;

	public function get_vendor_key(): string;

	public function get_field_id(): string;
}
