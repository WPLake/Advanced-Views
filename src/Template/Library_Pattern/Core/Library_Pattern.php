<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

defined( 'ABSPATH' ) || exit;

interface Library_Pattern {
	public function get_name(): string;

	public function get_auto_discover_name(): string;
}
