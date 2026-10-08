<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Container\Facade;

use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerInterface;

defined( 'ABSPATH' ) || exit;

abstract class Factory_Facade {
	private ContainerInterface $container;

	public function __construct( ContainerInterface $container ) {
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
}
