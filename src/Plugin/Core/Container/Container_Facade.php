<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Container;

use Org\Wplake\Advanced_Views\Vendors\DI\Container;

defined( 'ABSPATH' ) || exit;

abstract class Container_Facade {
	protected Container $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * @template Instance of object
	 *
	 * @param class-string<Instance> $class_name
	 *
	 * @return Instance
	 */
	protected function resolve( string $class_name ): object {
		return $this->container->get( $class_name );
	}

	protected function wire( string $class_name, object $instance ): void {
		$this->container->set( $class_name, $instance );
	}
}
