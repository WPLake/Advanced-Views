<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Container;

defined( 'ABSPATH' ) || exit;

interface Instance_Container {
	/**
	 * @template Instance of object
	 *
	 * @param class-string<Instance> $class_name
	 *
	 * @return Instance
	 */
	public function resolve( string $class_name ): object;
}
