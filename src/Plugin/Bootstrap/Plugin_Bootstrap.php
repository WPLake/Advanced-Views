<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Resolver\Plugin_Assets;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\State_Report;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\Usage_Report;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Environment;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Translations;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Page;

class Plugin_Bootstrap extends Bootstrap_Base {
	public static function get_type_definitions(): array {
		// Plugin instance itself is wired by the loader (Lite or Pro edition).
		return array(
			Plugin_Assets::class => Plugin::class,
		);
	}

	public static function get_instance_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Plugin_Factory::class );

		return array(
			Usage_Report::class => fn() => $factory->usage_report(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			State_Report::class,
			Usage_Report::class,
			Settings_Page::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Plugin_Factory::class );

		return array(
			Plugin_Environment::class  => fn() => $factory->plugin_environment(),
			Plugin_Translations::class => fn() => $factory->plugin_translations(),
		);
	}
}
