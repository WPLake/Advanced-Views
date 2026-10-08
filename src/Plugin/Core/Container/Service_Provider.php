<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Container;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerInterface;

interface Service_Provider {
	/**
	 * Static, as the container is built from them before any instance exists.
	 *
	 * @return array<class-string, class-string> the id (an abstract/Lite class) => the class the container resolves instead
	 */
	public static function get_type_definitions(): array;

	/**
	 * @return array<class-string, Closure>
	 */
	public static function get_instance_factories( ContainerInterface $container ): array;
}
