<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Container;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Vendors\DI\Container;
use Org\Wplake\Advanced_Views\Vendors\DI\ContainerBuilder;
use Org\Wplake\Advanced_Views\Vendors\DI\Definition\Reference;
use function Org\Wplake\Advanced_Views\Vendors\DI\get;

abstract class Container_Factory {
	/**
	 * @param class-string<Service_Provider>[] $provider_classes
	 */
	public static function build( array $provider_classes ): Container {
		$builder = new ContainerBuilder();

		$builder->useAutowiring( true );
		$builder->useAnnotations( false );

		$definitions = self::compose_type_definitions( $provider_classes );
		$builder->addDefinitions( $definitions );

		$container = $builder->build();

		self::wire_instance_factories( $container, $provider_classes );

		return $container;
	}

	/**
	 * PHP-DI Reference is just a class pointer, not an instance.
	 *
	 * @param class-string<Service_Provider>[] $providers
	 *
	 * @return array<class-string, Reference>
	 */
	protected static function compose_type_definitions( array $providers ): array {
		$type_definitions = array();

		foreach ( $providers as $provider ) {
			$type_definitions = array_merge(
				$type_definitions,
				$provider::get_type_definitions()
			);
		}

		return array_map(
			fn( string $class_name ): Reference => get( $class_name ),
			$type_definitions
		);
	}

	/**
	 * @param class-string<Service_Provider>[] $providers
	 */
	protected static function wire_instance_factories( Container $container, array $providers ): void {
		foreach ( $providers as $provider ) {
			$instances = $provider::get_instance_factories( $container );

			foreach ( $instances as $id => $instance ) {
				$container->set( $id, $instance );
			}
		}
	}
}
