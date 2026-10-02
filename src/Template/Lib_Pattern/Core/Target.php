<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Everything a library needs to place its code/markup, already resolved by an adapter.
 * Knows nothing about Layouts/Selections.
 */
class Target {
	public string $css_selector;
	public string $js_selector;
	// JS variable the library code works with.
	public string $var_name;
	// Unique key of the target (used as the code piece name).
	public string $field_id;
	public string $bem_name;
	// BEM class of the target element, without the leading dot.
	public string $bem_element;
	// CSS id of the root container, without the leading hash.
	public string $root_id;
	// Template variable of the current item (for markup of items).
	public string $item_prefix;
	// The value holds many items (e.g. gallery), vs a single one (e.g. image).
	public bool $is_multiple;
	// The code must run for every element, as the target is a repeater sub-field.
	public bool $is_in_repeater;
	// Library-specific mode, resolved by the adapter. Empty means default.
	public string $variant;

	public function __construct(
		string $css_selector,
		string $js_selector,
		string $var_name,
		string $field_id,
		string $bem_name,
		string $bem_element,
		string $root_id,
		bool $is_multiple,
		bool $is_in_repeater,
		string $item_prefix = '',
		string $variant = ''
	) {
		$this->css_selector   = $css_selector;
		$this->js_selector    = $js_selector;
		$this->var_name       = $var_name;
		$this->field_id       = $field_id;
		$this->bem_name       = $bem_name;
		$this->bem_element    = $bem_element;
		$this->root_id        = $root_id;
		$this->is_multiple    = $is_multiple;
		$this->is_in_repeater = $is_in_repeater;
		$this->item_prefix    = $item_prefix;
		$this->variant        = $variant;
	}
}
