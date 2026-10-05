<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;

abstract class Module_Loader {
	private Route_Detector $route_detector;

	public function __construct() {
		$this->route_detector = new Route_Detector();
	}

	/**
	 * @param Hookable[] $hookable
	 */
	protected function load_hookable( array $hookable ): void {
		foreach ( $hookable as $item ) {
			$item->set_hooks( $this->route_detector );
		}
	}

	/**
	 * Registers hookables that depend on another plugin being active (e.g. Elementor).
	 * Deferred to 'plugins_loaded' and conditional.
	 *
	 * @param callable():bool $is_active
	 * @param callable():array<int, Hookable> $make_hookable
	 */
	protected function add_plugin_extension( callable $is_active, callable $make_hookable ): void {
		add_action(
			'plugins_loaded',
			function () use ( $is_active, $make_hookable ): void {
				if ( $is_active() ) {
					$this->load_hookable( $make_hookable() );
				}
			},
			11
		);
	}
}
