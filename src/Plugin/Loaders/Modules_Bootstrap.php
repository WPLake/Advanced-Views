<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;
use function Org\Wplake\Advanced_Views\Utils\flat_map;

class Modules_Bootstrap extends Container_Facade {
	/**
	 * @var Module_Bootstrap[]
	 */
	protected array $bootstraps;
	protected Route_Detector $route_detector;

	/**
	 * @param Module_Bootstrap[] $bootstraps
	 */
	public function __construct( Container $container, array $bootstraps, Route_Detector $route_detector ) {
		parent::__construct( $container );

		$this->bootstraps     = $bootstraps;
		$this->route_detector = $route_detector;
	}

	public function bootstrap(): void {
		$this->wire_instance_factories();

		$resolved_classes = $this->resolve_hookable_classes();
		$instances        = $this->resolve_hookable_instances();
		$hookables        = array_merge( $resolved_classes, $instances );

		$this->set_route_hooks( $hookables );

		$this->add_plugin_extensions();
	}

	protected function wire_instance_factories(): void {
		foreach ( $this->bootstraps as $bootstrap ) {
			$instances = $bootstrap->get_instance_factories();

			foreach ( $instances as $id => $instance ) {
				$this->wire( $id, $instance );
			}
		}
	}

	/**
	 * @return Hookable[]
	 */
	protected function resolve_hookable_classes(): array {
		$get_classes = fn( Module_Bootstrap $bootstrap ): array => $bootstrap->get_hookable_classes();
		$classes     = flat_map( $this->bootstraps, $get_classes );

		/**
		 * @param class-string<Hookable> $class_name
		 */
		$has_route_hooks = fn( string $class_name ): bool => $class_name::has_route_hooks( $this->route_detector );
		$routed_classes  = array_filter( $classes, $has_route_hooks );
		$routed_classes  = array_values( $routed_classes );

		$resolve_class = fn( string $class_name ): Hookable => $this->resolve( $class_name );
		return array_map( $resolve_class, $routed_classes );
	}

	/**
	 * @return Hookable[]
	 */
	protected function resolve_hookable_instances(): array {
		$create_instances = fn( Module_Bootstrap $bootstrap ): array => $this->create_bootstrap_instances( $bootstrap );

		return flat_map( $this->bootstraps, $create_instances );
	}

	/**
	 * @return Hookable[]
	 */
	protected function create_bootstrap_instances( Module_Bootstrap $bootstrap ): array {
		$factories = $bootstrap->get_hookable_factories();

		return $this->create_hookable_instances( $factories );
	}

	protected function add_plugin_extensions(): void {
		add_action(
			'plugins_loaded',
			function (): void {
				foreach ( $this->bootstraps as $bootstrap ) {
					$factories = $bootstrap->resolve_extension_hookables();
					$hookables = $this->create_hookable_instances( $factories );

					$this->set_route_hooks( $hookables );
				}
			},
			11
		);
	}

	/**
	 * @param array<class-string<Hookable>, Closure():Hookable> $factories
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
