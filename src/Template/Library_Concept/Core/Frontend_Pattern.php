<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Concept\Core;

defined( 'ABSPATH' ) || exit;

interface Frontend_Pattern {
	public static function get_id(): string;

	public static function get_label(): string;
}
