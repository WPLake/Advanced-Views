<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

final class Composite_Attribute {
	/**
	 * @var array<string|object>
	 */
	public array $items      = array();
	public string $delimiter = '';
}
