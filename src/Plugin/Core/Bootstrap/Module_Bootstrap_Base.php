<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;

defined( 'ABSPATH' ) || exit;

abstract class Module_Bootstrap_Base extends Container_Facade implements Module_Bootstrap {
	public function wire_factories(): void {}

	public function get_extension_hookables(): array {
		return array();
	}
}
