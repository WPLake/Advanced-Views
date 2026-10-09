<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Module;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;

defined( 'ABSPATH' ) || exit;

abstract class Bootstrap_Base implements Instance_Provider, Actor_Provider {
	public static function get_type_definitions(): array {
		return array();
	}

	public static function get_instance_factories( Instance_Container $container ): array {
		return array();
	}

	public static function get_actor_classes(): array {
		return array();
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		return array();
	}

	public static function resolve_extension_actors( Instance_Container $container ): array {
		return array();
	}
}
