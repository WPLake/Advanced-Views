<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Module;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;

interface Actor_Provider {
	/**
	 * @return class-string<Actor>[]
	 */
	public static function get_actor_classes(): array;

	/**
	 * Hookables that can't be autowired by the container, as they need arguments (e.g. the CPT name).
	 * Keyed by the class, so the loader can check has_route_hooks() before creating an instance.
	 *
	 * @return array<class-string<Actor>, Closure(): Actor>
	 */
	public static function get_actor_factories( Instance_Container $container ): array;

	/**
	 * Hookables that depend on another plugin being active, so the module checks that itself (e.g. did_action()).
	 * Called on 'plugins_loaded', after the hookables.
	 *
	 * @return array<class-string<Actor>, Closure(): Actor>
	 */
	public static function resolve_extension_actors( Instance_Container $container ): array;
}
