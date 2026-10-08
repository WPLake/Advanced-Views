<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Service_Provider;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Provider\Hookable_Provider;
use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerInterface;

defined( 'ABSPATH' ) || exit;

abstract class Module_Base implements Service_Provider, Hookable_Provider {
	public static function get_type_definitions(): array {
		return array();
	}

	public static function get_instance_factories( ContainerInterface $container ): array {
		return array();
	}

	public static function get_hookable_classes(): array {
		return array();
	}

	public static function get_hookable_factories( ContainerInterface $container ): array {
		return array();
	}

	public static function resolve_extension_hookables( ContainerInterface $container ): array {
		return array();
	}
}
