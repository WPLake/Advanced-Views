<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Provider;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerInterface;

interface Hookable_Provider {
	/**
	 * @return class-string<Hookable>[]
	 */
	public static function get_hookable_classes(): array;

	/**
	 * Hookables that can't be autowired by the container, as they need arguments (e.g. the CPT name).
	 * Keyed by the class, so the loader can check has_route_hooks() before creating an instance.
	 *
	 * @return Closure
	 */
	public static function get_hookable_factories( ContainerInterface $container ): array;

	/**
	 * Hookables that depend on another plugin being active, so the module checks that itself (e.g. did_action()).
	 * Called on 'plugins_loaded', after the hookables.
	 *
	 * @return Closure
	 */
	public static function resolve_extension_hookables( ContainerInterface $container ): array;
}
