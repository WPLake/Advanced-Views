<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;

interface Module_Bootstrap {
	/**
	 * @return Hookable[]
	 */
	public function get_hookables( Route_Detector $route_detector ): array;

	/**
	 * Hookables that depend on another plugin being active, keyed by the action fired when that plugin is loaded
	 * (e.g. 'elementor/loaded'). Must be called after get_hookables().
	 *
	 * @return array<string, callable():Hookable[]>
	 */
	public function get_plugin_extensions(): array;
}
