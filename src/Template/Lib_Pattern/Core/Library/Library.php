<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library;

defined( 'ABSPATH' ) || exit;

interface Library {
	public function get_name(): string;

	public function get_auto_discover_name(): string;

	public function is_web_component_required(): bool;
}
