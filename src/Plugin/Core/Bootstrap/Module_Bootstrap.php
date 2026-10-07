<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;

interface Module_Bootstrap {
	/**
	 * Static, as the container is built from them before any instance exists.
	 *
	 * @return array<class-string, class-string> the id (an abstract/Lite class) => the class the container resolves instead
	 */
	public static function get_type_definitions(): array;

	/**
	 * The container factories (closures) or instances of the module, by their ids. Must be wired before get_hookables().
	 *
	 * @return array<class-string, Closure>
	 */
	public function get_instance_factories(): array;

	/**
	 * @return Hookable[]
	 */
	public function resolve_hookables( Route_Detector $route_detector ): array;

	/**
	 * Hookables that depend on another plugin being active, so the module checks that itself (e.g. did_action()).
	 * Called on 'plugins_loaded', after get_hookables().
	 *
	 * @return Hookable[]
	 */
	public function resolve_extension_hookables(): array;
}
