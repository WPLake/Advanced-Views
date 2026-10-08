<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Repository\Instance_Repository;

abstract class Module_Loader {
	private Instance_Repository $repository;
	private Route_Detector $route_detector;

	public function __construct( Instance_Repository $repository ) {
		$this->repository     = $repository;
		$this->route_detector = new Route_Detector();
	}

	/**
	 * @template Instance of object
	 *
	 * @param class-string<Instance> $class_name
	 *
	 * @return Instance
	 */
	protected function resolve( string $class_name ): object {
		return $this->repository->resolve( $class_name );
	}

	protected function wire( string $id, object $instance ): void {
		$this->repository->wire( $id, $instance );
	}

	/**
	 * @param Actor[] $hookable
	 */
	protected function load_hookable( array $hookable ): void {
		foreach ( $hookable as $item ) {
			$item->set_route_hooks( $this->route_detector );
		}
	}
}
