<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Value\Core;

defined( 'ABSPATH' ) || exit;

abstract class List_Value implements Field_Value {
	const ITEMS = 'items';

	/**
	 * Lists override it with their specific item type.
	 *
	 * @return Field_Value[]
	 */
	abstract public function get_items(): array;

	public function get_properties(): array {
		return array(
			self::ITEMS => fn() => $this->get_items(),
		);
	}
}
