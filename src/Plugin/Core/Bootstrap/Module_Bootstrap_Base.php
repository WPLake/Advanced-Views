<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Facade;

defined( 'ABSPATH' ) || exit;

abstract class Module_Bootstrap_Base extends Factory_Facade implements Module_Bootstrap {
	public static function get_type_definitions(): array {
		return array();
	}

	public function get_instance_factories(): array {
		return array();
	}

	public function get_hookable_classes(): array {
		return array();
	}

	public function get_hookable_factories(): array {
		return array();
	}

	public function resolve_extension_hookables(): array {
		return array();
	}
}
