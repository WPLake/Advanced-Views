<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;

interface Module_Bootstrap {
	/**
	 * Static, as the container is built from them before any instance exists.
	 *
	 * @return array<class-string, class-string> the id (an abstract/Lite class) => the class the container resolves instead
	 */
	public static function get_type_definitions(): array;

	/**
	 * The container factories (closures) or instances of the module, by their ids. Must be wired before get_hookable_factories().
	 *
	 * @return array<class-string, Closure>
	 */
	public function get_instance_factories(): array;

	/**
	 * @return class-string<Hookable>[]
	 */
	public function get_hookable_classes(): array;

	/**
	 * Hookables that can't be autowired by the container, as they need arguments (e.g. the CPT name).
	 * Keyed by the class, so the loader can check has_route_hooks() before creating an instance.
	 *
	 * @return array<class-string<Hookable>, Closure():Hookable>
	 */
	public function get_hookable_factories(): array;

	/**
	 * Hookables that depend on another plugin being active, so the module checks that itself (e.g. did_action()).
	 * Called on 'plugins_loaded', after the hookables.
	 *
	 * @return Hookable[]
	 */
	public function resolve_extension_hookables(): array;
}
