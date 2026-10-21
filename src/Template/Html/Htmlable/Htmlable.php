<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Htmlable;

defined( 'ABSPATH' ) || exit;

/**
 * An object printable as is by Tags_Renderer (e.g. a template token): its string form is output without escaping.
 * Stands in for the PHP 8 only Stringable.
 */
interface Htmlable {
	public function to_html(): string;
}
