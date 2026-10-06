<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Core\Settings;

defined( 'ABSPATH' ) || exit;

interface Meta_Field_Rule {
	const DYNAMIC_VALUE_POST            = '$post$';
	const DYNAMIC_VALUE_POST_FIELD      = '$post$.';
	const DYNAMIC_VALUE_NOW             = '$now$';
	const DYNAMIC_VALUE_QUERY           = '$query$.';
	const DYNAMIC_VALUE_CUSTOM_ARGUMENT = '$custom-arguments$.';

	public function get_comparison(): string;

	public function set_comparison( string $comparison ): void;

	public function get_vendor_name(): string;

	public function get_field_id(): string;

	public function resolve_value(): string;

	public function is_dynamic_value(): bool;
}
