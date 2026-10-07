<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Hookable;

defined( 'ABSPATH' ) || exit;

interface Hookable {
	public function has_route_hooks( Route_Detector $route_detector ): void;

	public function set_route_hooks( Route_Detector $route_detector ): void;
}
