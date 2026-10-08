<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Facade\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;

abstract class Module_Loader extends Container_Facade {
	private Route_Detector $route_detector;

	public function __construct( Container $container ) {
		parent::__construct( $container );

		$this->route_detector = new Route_Detector();
	}

	/**
	 * @param Hookable[] $hookable
	 */
	protected function load_hookable( array $hookable ): void {
		foreach ( $hookable as $item ) {
			$item->set_route_hooks( $this->route_detector );
		}
	}
}
