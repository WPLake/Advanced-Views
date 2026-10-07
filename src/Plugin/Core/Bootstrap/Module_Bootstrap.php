<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;

interface Module_Bootstrap {
	/**
	 * The container factories (closures) or instances of the module, by their ids. Must be wired before get_hookables().
	 *
	 * @return array<class-string, \Closure|object>
	 */
	public function get_wires(): array;

	/**
	 * @return Hookable[]
	 */
	public function get_hookables( Route_Detector $route_detector ): array;

	/**
	 * Hookables that depend on another plugin being active, so the module checks that itself (e.g. did_action()).
	 * Called on 'plugins_loaded', after get_hookables().
	 *
	 * @return Hookable[]
	 */
	public function get_extension_hookables(): array;
}
