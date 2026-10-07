<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;
use Org\Wplake\Advanced_Views\Vendors\DI\ContainerBuilder;
use Org\Wplake\Advanced_Views\Vendors\DI\Definition\Reference;
use function Org\Wplake\Advanced_Views\Vendors\DI\get;

class Container_Factory {
	/**
	 * @var class-string<Module_Bootstrap>[]
	 */
	protected array $bootstrap_classes;

	/**
	 * @param class-string<Module_Bootstrap>[] $bootstrap_classes
	 */
	public function __construct( array $bootstrap_classes ) {
		$this->bootstrap_classes = $bootstrap_classes;
	}

	public function build(): Container {
		$builder = new ContainerBuilder();

		$builder->useAutowiring( true );
		$builder->useAnnotations( false );

		$definitions = $this->compose_type_definitions();
		$builder->addDefinitions( $definitions );

		$container = $builder->build();

		$this->wire_instance_factories( $container );

		return $container;
	}

	/**
	 * PHP-DI Reference is just a class pointer, not an instance.
	 *
	 * @return array<class-string, Reference>
	 */
	protected function compose_type_definitions(): array {
		$type_definitions = array();

		foreach ( $this->bootstrap_classes as $bootstrap_class ) {
			$type_definitions = array_merge(
				$type_definitions,
				$bootstrap_class::get_type_definitions()
			);
		}

		return array_map(
			fn( string $class_name ): Reference => get( $class_name ),
			$type_definitions
		);
	}

	protected function wire_instance_factories( Container $container ): void {
		foreach ( $this->bootstrap_classes as $bootstrap_class ) {
			$bootstrap = $container->get( $bootstrap_class );
			$instances = $bootstrap->get_instance_factories();

			foreach ( $instances as $id => $instance ) {
				$container->set( $id, $instance );
			}
		}
	}
}
