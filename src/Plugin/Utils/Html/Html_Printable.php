<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

defined( 'ABSPATH' ) || exit;

/**
 * An object printable as is by Tags_Renderer (e.g. a template token): its string form is output without escaping.
 * Stands in for the PHP 8 only Stringable.
 */
interface Html_Printable {
	public function to_html(): string;
}
