<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loader;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Actor_Provider;
use Org\Wplake\Advanced_Views\Plugin\Environment\Plugin_Tester;

final class Actor_Bootstrap {
	protected Instance_Container $container;
	protected Route_Detector $route_detector;
	protected bool $is_test_bootstrap;

	public function __construct( Instance_Container $container, Route_Detector $route_detector ) {
		$this->container         = $container;
		$this->route_detector    = $route_detector;
		$this->is_test_bootstrap = Plugin_Tester::is_test_bootstrap();
	}

	/**
	 * @param class-string<Actor_Provider>[] $provider_classes
	 */
	public function bootstrap( array $provider_classes ): void {
		$class_actors   = $this->resolve_actor_classes( $provider_classes );
		$factory_actors = $this->resolve_factory_actors( $provider_classes );

		$actors = array_merge( $class_actors, $factory_actors );

		// in test bootstrap, all the actors are created but not bootstrap -
		// to ensure the right creation order & no recursion dependency.
		if ( ! $this->is_test_bootstrap ) {
			$this->set_route_hooks( $actors );

			$plugins_loaded_callback = fn() => $this->bootstrap_extension_actors( $provider_classes );
			add_action(
				'plugins_loaded',
				$plugins_loaded_callback,
				11
			);
		}
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
				if ( $this->is_actor_active( $actor_class ) ) {
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
	 * @param array<class-string<Actor>, Closure(): Actor> $factories
	 *
	 * @return Actor[]
	 */
	protected function create_actors( array $factories ): array {
		$routed_factories = array_filter( $factories, array( $this, 'is_actor_active' ), ARRAY_FILTER_USE_KEY );
		$create_actor     = fn( Closure $factory ): Actor => $factory();

		$actors = array_map( $create_actor, $routed_factories );

		return array_values( $actors );
	}

	/**
	 * @param class-string<Actor> $actor_class
	 */
	protected function is_actor_active( string $actor_class ): bool {
		return $actor_class::has_route_hooks( $this->route_detector ) ||
			$this->is_test_bootstrap;
	}

	/**
	 * @param Actor[] $actors
	 */
	protected function set_route_hooks( array $actors ): void {
		foreach ( $actors as $actor ) {
			$actor->set_route_hooks( $this->route_detector );
		}
	}

	/**
	 * @param class-string<Actor_Provider>[] $providers
	 */
	protected function bootstrap_extension_actors( array $providers ): void {
		foreach ( $providers as $provider ) {
			$factories = $provider::resolve_extension_actors( $this->container );
			$actors    = $this->create_actors( $factories );

			$this->set_route_hooks( $actors );
		}
	}
}
