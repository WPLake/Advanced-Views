<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

defined( 'ABSPATH' ) || exit;

interface Frontend_Pattern {
	public static function get_id(): string;

	public static function get_label(): string;
}
