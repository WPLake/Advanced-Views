<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration\Ajax_Select;

defined( 'ABSPATH' ) || exit;

interface Ajax_Select_Source {
	/**
	 * @return array<string,string> value => label
	 */
	public function get_items(): array;

	/**
	 * Empty string means no 'Add new' link.
	 */
	public function get_add_new_url(): string;

	public function get_add_new_label(): string;
}
