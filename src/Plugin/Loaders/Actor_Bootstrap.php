<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Actor_Provider;

final class Actor_Bootstrap {
	protected Instance_Container $container;
	protected Route_Detector $route_detector;

	public function __construct( Instance_Container $container, Route_Detector $route_detector ) {
		$this->container      = $container;
		$this->route_detector = $route_detector;
	}

	/**
	 * @param class-string<Actor_Provider>[] $provider_classes
	 */
	public function bootstrap( array $provider_classes ): void {
		$class_actors   = $this->resolve_actor_classes( $provider_classes );
		$factory_actors = $this->resolve_factory_actors( $provider_classes );
		$actors         = array_merge( $class_actors, $factory_actors );

		$this->set_route_hooks( $actors );

		$this->add_plugin_extensions( $provider_classes );
	}

	/**
	 * @param class-string<Actor_Provider>[] $provider_classes
	 *
	 * @return Actor[]
	 */
	protected function resolve_actor_classes( array $provider_classes ): array {
		$actors = array();

		foreach ( $provider_classes as $provider ) {
			foreach ( $provider::get_actor_classes() as $actor_class ) {
				if ( $actor_class::has_route_hooks( $this->route_detector ) ) {
					$actors[] = $this->container->resolve( $actor_class );
				}
			}
		}

		return $actors;
	}

	/**
	 * @param class-string<Actor_Provider>[] $provider_classes
	 *
	 * @return Actor[]
	 */
	protected function resolve_factory_actors( array $provider_classes ): array {
		$actors = array();

		foreach ( $provider_classes as $provider ) {
			$actors = array_merge( $actors, $this->create_provider_actors( $provider ) );
		}

		return $actors;
	}

	/**
	 * @param class-string<Actor_Provider> $provider
	 *
	 * @return Actor[]
	 */
	protected function create_provider_actors( string $provider ): array {
		$factories = $provider::get_actor_factories( $this->container );

		return $this->create_actors( $factories );
	}

	/**
	 * @param class-string<Actor_Provider>[] $providers
	 */
	protected function add_plugin_extensions( array $providers ): void {
		add_action(
			'plugins_loaded',
			function () use ( $providers ): void {
				foreach ( $providers as $provider ) {
					$factories = $provider::resolve_extension_actors( $this->container );
					$actors    = $this->create_actors( $factories );

					$this->set_route_hooks( $actors );
				}
			},
			11
		);
	}

	/**
	 * @param array<class-string<Actor>, Closure(): Actor> $factories
	 *
	 * @return Actor[]
	 */
	protected function create_actors( array $factories ): array {
		/**
		 * @param class-string<Actor> $class_name
		 */
		$has_route_hooks  = fn( string $class_name ): bool => $class_name::has_route_hooks( $this->route_detector );
		$routed_factories = array_filter( $factories, $has_route_hooks, ARRAY_FILTER_USE_KEY );
		$create_actor     = fn( Closure $factory ): Actor => $factory();

		$actors = array_map( $create_actor, $routed_factories );

		return array_values( $actors );
	}

	/**
	 * @param Actor[] $actors
	 */
	protected function set_route_hooks( array $actors ): void {
		foreach ( $actors as $actor ) {
			$actor->set_route_hooks( $this->route_detector );
		}
	}
}
