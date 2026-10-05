<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;

defined( 'ABSPATH' ) || exit;

abstract class Module_Bootstrap_Base implements Module_Bootstrap {
	protected Container $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Registers hookables that depend on another plugin being active (e.g. Elementor).
	 * Deferred to 'plugins_loaded' and conditional.
	 *
	 * @param callable():bool $is_active
	 * @param callable():array<int, Hookable> $make_hookables
	 */
	protected function add_plugin_extension(
		Route_Detector $route_detector,
		callable $is_active,
		callable $make_hookables
	): void {
		add_action(
			'plugins_loaded',
			function () use ( $route_detector, $is_active, $make_hookables ): void {
				if ( ! $is_active() ) {
					return;
				}

				foreach ( $make_hookables() as $hookable ) {
					$hookable->set_hooks( $route_detector );
				}
			},
			11
		);
	}
}
