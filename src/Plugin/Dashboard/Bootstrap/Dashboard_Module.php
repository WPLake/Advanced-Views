<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Bar;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Pages;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Demo_Importer;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools_Page;

class Dashboard_Module extends Module_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Admin_Pages::class => fn() => $container->resolve( Dashboard_Factory::class )
				->admin_pages(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Admin_Pages::class,
			Tools_Page::class,
			Demo_Importer::class,
			Live_Reloader::class,
			Live_Reloader_Component::class,
			Admin_Bar::class,
		);
	}
}
