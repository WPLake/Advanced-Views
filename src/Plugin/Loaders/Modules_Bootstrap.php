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

final class Modules_Bootstrap extends Container_Facade {
	protected Route_Detector $route_detector;

	public function __construct( Container $container, Route_Detector $route_detector ) {
		parent::__construct( $container );

		$this->route_detector = $route_detector;
	}

	/**
	 * @param class-string<Module_Bootstrap>[] $bootstrap_classes
	 */
	public function bootstrap( array $bootstrap_classes ): void {
		$resolve_bootstrap = fn( string $class_name ): Module_Bootstrap => $this->resolve( $class_name );
		$bootstraps        = array_map( $resolve_bootstrap, $bootstrap_classes );

		$resolved_classes = $this->resolve_hookable_classes( $bootstraps );
		$instances        = $this->resolve_hookable_instances( $bootstraps );
		$hookables        = array_merge( $resolved_classes, $instances );

		$this->set_route_hooks( $hookables );

		$this->add_plugin_extensions( $bootstraps );
	}

	/**
	 * @param Module_Bootstrap[] $bootstraps
	 *
	 * @return Hookable[]
	 */
	protected function resolve_hookable_classes( array $bootstraps ): array {
		$get_classes = fn( Module_Bootstrap $bootstrap ): array => $bootstrap->get_hookable_classes();
		$classes     = flat_map( $bootstraps, $get_classes );

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
	 * @param Module_Bootstrap[] $bootstraps
	 *
	 * @return Hookable[]
	 */
	protected function resolve_hookable_instances( array $bootstraps ): array {
		$create_instances = fn( Module_Bootstrap $bootstrap ): array => $this->create_bootstrap_instances( $bootstrap );

		return flat_map( $bootstraps, $create_instances );
	}

	/**
	 * @return Hookable[]
	 */
	protected function create_bootstrap_instances( Module_Bootstrap $bootstrap ): array {
		$factories = $bootstrap->get_hookable_factories();

		return $this->create_hookable_instances( $factories );
	}

	/**
	 * @param Module_Bootstrap[] $bootstraps
	 */
	protected function add_plugin_extensions( array $bootstraps ): void {
		add_action(
			'plugins_loaded',
			function () use ( $bootstraps ): void {
				foreach ( $bootstraps as $bootstrap ) {
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
