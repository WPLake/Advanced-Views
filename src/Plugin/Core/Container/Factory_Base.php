<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Container;

defined( 'ABSPATH' ) || exit;

abstract class Factory_Base {
	private Instance_Container $container;

	public function __construct( Instance_Container $container ) {
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
		return $this->container->resolve( $class_name );
	}
}
