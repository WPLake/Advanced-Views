<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Loaders\Repository\Instance_Repository;

abstract class Module_Loader {
	private Instance_Repository $repository;

	public function __construct( Instance_Repository $repository ) {
		$this->repository = $repository;
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

	/**
	 * @template Instance of object
	 *
	 * @param class-string<Instance> $class_name
	 * @param Instance $instance
	 */
	protected function wire( string $class_name, object $instance ): void {
		$this->repository->wire( $class_name, $instance );
	}
}
