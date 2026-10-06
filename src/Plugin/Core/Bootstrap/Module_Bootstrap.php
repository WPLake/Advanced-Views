<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;

interface Module_Bootstrap {
	/**
	 * Registers the container factories of the module's instances. Must be called before get_hookables().
	 */
	public function wire_instance_factories(): void;

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
