<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Hookable;

defined( 'ABSPATH' ) || exit;

interface Hookable {
	public static function has_route_hooks( Route_Detector $route_detector ): bool;

	public function set_route_hooks( Route_Detector $route_detector ): void;
}
