<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Value\Core;

defined( 'ABSPATH' ) || exit;

interface Field_Value {
	/**
	 * @return array<string, callable(): mixed>
	 */
	public function get_properties(): array;
}
