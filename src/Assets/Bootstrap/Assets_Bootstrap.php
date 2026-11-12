<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Admin_Assets;
use Org\Wplake\Advanced_Views\Assets\Runtime\Inline_Asset_Injector;
use Org\Wplake\Advanced_Views\Assets\Runtime\Script\Interactivity_Loader;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Front_Page_Buffer;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Inline_Style_Injector;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind_Integrator;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;

class Assets_Bootstrap extends Bootstrap_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Tailwind_Integrator::class => fn() => $container->resolve( Assets_Factory::class )
				->tailwind_integrator(),
			Admin_Assets::class        => fn() => $container->resolve( Assets_Factory::class )
				->admin_assets(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Front_Page_Buffer::class,
			Inline_Asset_Injector::class,
			Inline_Style_Injector::class,
			Admin_Assets::class,
			Interactivity_Loader::class,
		);
	}
}
