<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element;

defined( 'ABSPATH' ) || exit;

abstract class List_Element implements Markup_Element {
	const ITEMS = 'items';

	/**
	 * Lists override it with their specific item type.
	 *
	 * @return Markup_Element[]
	 */
	abstract public function get_items(): array;

	public function get_properties(): array {
		return array(
			self::ITEMS => fn() => $this->get_items(),
		);
	}
}
