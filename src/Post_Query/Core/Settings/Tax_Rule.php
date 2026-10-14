<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Core\Settings;

defined( 'ABSPATH' ) || exit;

interface Tax_Rule {
	public function get_relation(): string;

	/**
	 * @return Term_Settings[]
	 */
	public function get_terms(): array;
}
