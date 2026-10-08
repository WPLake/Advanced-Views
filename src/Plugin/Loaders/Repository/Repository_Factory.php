<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders\Repository;

defined( 'ABSPATH' ) || exit;

use;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Instance_Provider;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;
use Org\Wplake\Advanced_Views\Vendors\DI\ContainerBuilder;
use Org\Wplake\Advanced_Views\Vendors\DI\Definition\Reference;
use function Org\Wplake\Advanced_Views\Vendors\DI\get;

final class Repository_Factory {
	/**
	 * @var class-string<Instance_Provider>[]
	 */
	private array $provider_classes;
	/**
	 * @param class-string<Instance_Provider>[] $provider_classes
	 */
	private function __construct( array $provider_classes ) {
		$this->provider_classes = $provider_classes;
	}

	/**
	 * @param class-string<Instance_Provider>[] $provider_classes
	 */
	public static function build( array $provider_classes ): Instance_Repository {
		$factory = new self( $provider_classes );

		$container  = $factory->build_container();
		$repository = new Instance_Repository( $container );

		$container->set( Instance_Container::class, $repository );
		$factory->wire_instance_factories( $repository );

		return $repository;
	}

	protected function build_container(): Container {
		$builder = new ContainerBuilder();

		$builder->useAutowiring( true );
		$builder->useAnnotations( false );

		$definitions = $this->compose_type_definitions();
		$builder->addDefinitions( $definitions );

		return $builder->build();
	}

	/**
	 * PHP-DI Reference is just a class pointer, not an instance.
	 *
	 * @return array<class-string, Reference>
	 */
	protected function compose_type_definitions(): array {
		$type_definitions = array();

		foreach ( $this->provider_classes as $provider ) {
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
	protected function wire_instance_factories( Instance_Repository $repository ): void {
		foreach ( $this->provider_classes as $provider ) {
			$instances = $provider::get_instance_factories( $repository );

			foreach ( $instances as $id => $factory ) {
				$repository->wire( $id, $factory );
			}
		}
	}
}
