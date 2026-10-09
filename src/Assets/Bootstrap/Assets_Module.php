<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Admin_Assets;
use Org\Wplake\Advanced_Views\Assets\Resolver\Asset_Resolver;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;

class Assets_Module extends Module_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Front_Assets::class   => fn() => $container->resolve( Assets_Factory::class )
				->front_assets(),
			Admin_Assets::class   => fn() => $container->resolve( Assets_Factory::class )
				->admin_assets(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Front_Assets::class,
			Admin_Assets::class,
		);
	}
}
