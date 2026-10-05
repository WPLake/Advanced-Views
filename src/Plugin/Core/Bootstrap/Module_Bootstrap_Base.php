<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

use Org\Wplake\Advanced_Views\Vendors\DI\Container;

defined( 'ABSPATH' ) || exit;

abstract class Module_Bootstrap_Base implements Module_Bootstrap {
	protected Container $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function get_plugin_extensions(): array {
		return array();
	}
}
