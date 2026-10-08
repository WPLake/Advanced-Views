<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Hookable_Provider;

final class ookables_Bootstrap {
	protected Instance_Container $container;
	protected Route_Detector $route_detector;

	public function __construct( Instance_Container $container, Route_Detector $route_detector ) {
		$this->container      = $container;
		$this->route_detector = $route_detector;
	}

	/**
	 * @param class-string<Hookable_Provider>[] $provider_classes
	 */
	public function bootstrap( array $provider_classes ): void {
		$resolved_classes = $this->resolve_hookable_classes( $provider_classes );
		$instances        = $this->resolve_hookable_instances( $provider_classes );
		$hookables        = array_merge( $resolved_classes, $instances );

		$this->set_route_hooks( $hookables );

		$this->add_plugin_extensions( $provider_classes );
	}

	/**
	 * @param class-string<Hookable_Provider>[] $providers
	 *
	 * @return Hookable[]
	 */
	protected function resolve_hookable_classes( array $providers ): array {
		$hookables = array();

		foreach ( $providers as $provider ) {
			foreach ( $provider::get_hookable_classes() as $class_name ) {
				if ( $class_name::has_route_hooks( $this->route_detector ) ) {
					$hookables[] = $this->container->resolve( $class_name );
				}
			}
		}

		return $hookables;
	}

	/**
	 * @param class-string<Hookable_Provider>[] $providers
	 *
	 * @return Hookable[]
	 */
	protected function resolve_hookable_instances( array $providers ): array {
		$instances = array();

		foreach ( $providers as $provider ) {
			$instances = array_merge( $instances, $this->create_bootstrap_instances( $provider ) );
		}

		return $instances;
	}

	/**
	 * @param class-string<Hookable_Provider> $provider
	 *
	 * @return Hookable[]
	 */
	protected function create_bootstrap_instances( string $provider ): array {
		$factories = $provider::get_hookable_factories( $this->container );

		return $this->create_hookable_instances( $factories );
	}

	/**
	 * @param class-string<Hookable_Provider>[] $providers
	 */
	protected function add_plugin_extensions( array $providers ): void {
		add_action(
			'plugins_loaded',
			function () use ( $providers ): void {
				foreach ( $providers as $provider ) {
					$factories = $provider::resolve_extension_hookables( $this->container );
					$hookables = $this->create_hookable_instances( $factories );

					$this->set_route_hooks( $hookables );
				}
			},
			11
		);
	}

	/**
	 * @param Closure $factories
	 *
	 * @return Hookable[]
	 */
	protected function create_hookable_instances( array $factories ): array {
		/**
		 * @param class-string<Hookable> $class_name
		 */
		$has_route_hooks  = fn( string $class_name ): bool => $class_name::has_route_hooks( $this->route_detector );
		$routed_factories = array_filter( $factories, $has_route_hooks, ARRAY_FILTER_USE_KEY );
		$create_instance  = fn( Closure $factory ): Hookable => $factory();

		$instances = array_map( $create_instance, $routed_factories );

		return array_values( $instances );
	}

	/**
	 * @param Hookable[] $hookables
	 */
	protected function set_route_hooks( array $hookables ): void {
		foreach ( $hookables as $hookable ) {
			$hookable->set_route_hooks( $this->route_detector );
		}
	}
}
