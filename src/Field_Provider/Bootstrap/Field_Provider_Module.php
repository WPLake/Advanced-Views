<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;

class Field_Provider_Module extends Module_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Field_Provider_Factory::class );

		return array(
			Field_Provider_Cluster::class => fn() => $factory->provider_cluster(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Field_Provider_Cluster::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Field_Provider_Factory::class );

		return array(
			Integrations_Loader::class => fn() => $factory->integrations_loader(),
		);
	}
}
