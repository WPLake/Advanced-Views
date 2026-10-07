<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;

defined( 'ABSPATH' ) || exit;

abstract class Module_Bootstrap_Base extends Container_Facade implements Module_Bootstrap {
	public static function get_definitions(): array {
		return array();
	}

	public function get_wires(): array {
		return array();
	}

	public function get_hookables( Route_Detector $route_detector ): array {
		$hookable_classes = $this->get_hookable_classes();
		$resolved         = array_map(
			fn( string $class_name ): Hookable => $this->resolve( $class_name ),
			$hookable_classes
		);

		$instances = $this->get_hookable_instances( $route_detector );

		return array_merge( $resolved, $instances );
	}

	public function get_extension_hookables(): array {
		return array();
	}

	/**
	 * @return array<class-string<Hookable>>
	 */
	protected function get_hookable_classes(): array {
		return array();
	}

	/**
	 * Hookables that can't be resolved from the container, as they need arguments (e.g. the CPT name).
	 *
	 * @return Hookable[]
	 */
	protected function get_hookable_instances( Route_Detector $route_detector ): array {
		return array();
	}
}
