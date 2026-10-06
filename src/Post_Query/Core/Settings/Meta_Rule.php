<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Core\Settings;

defined( 'ABSPATH' ) || exit;

interface Meta_Rule {
	public function get_relation(): string;

	/**
	 * @return Meta_Field_Rule[]
	 */
	public function get_fields(): array;
}
