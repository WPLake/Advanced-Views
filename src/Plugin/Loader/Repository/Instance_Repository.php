<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loader\Repository;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;

final class Instance_Repository implements Instance_Container {
	private Container $container;
	/**
	 * @var array<class-string,object|callable(): object>
	 */
	private array $wired_instances;

	public function __construct( Container $container ) {
		$this->container       = $container;
		$this->wired_instances = array();
	}

	public function resolve( string $class_name ): object {
		return $this->container->get( $class_name );
	}

	/**
	 * @template Instance of object
	 *
	 * @param class-string<Instance> $class_name
	 * @param Instance|callable(): Instance $resolver
	 */
	public function wire( string $class_name, $resolver ): void {
		$this->wired_instances[ $class_name ] = $resolver;

		$this->container->set( $class_name, $resolver );
	}

	/**
	 * @return array<class-string,object|callable(): object>
	 */
	public function get_wired_instances(): array {
		return $this->wired_instances;
	}
}
