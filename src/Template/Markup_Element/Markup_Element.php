<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element;

defined( 'ABSPATH' ) || exit;

interface Markup_Element {
	/**
	 * @return array<string, callable(): mixed>
	 */
	public function get_properties(): array;
}
