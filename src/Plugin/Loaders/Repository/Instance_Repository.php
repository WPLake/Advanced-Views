<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders\Repository;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;

final class Instance_Repository implements Instance_Container {
	private Container $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function resolve( string $class_name ): object {
		return $this->container->get( $class_name );
	}

	/**
	 * @template Instance of object
	 *
	 * @param class-string<Instance> $class_name
	 * @param Instance|Closure(): Instance $resolver
	 */
	public function wire( string $class_name, $resolver ): void {
		$this->container->set( $class_name, $resolver );
	}
}
