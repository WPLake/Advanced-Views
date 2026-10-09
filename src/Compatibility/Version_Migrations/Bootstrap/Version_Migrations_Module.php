<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Compatibility\Migration\Version_Migrator;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Settings_Migrator;

final class Version_Migrations_Module extends Module_Base {
	public static function get_type_definitions(): array {
		return array( Cpt_Settings_Migrator::class => Version_Migrator::class );
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Version_Migrations_Factory::class );

		/**
		 * Migrations depend on the instances registered in the container by later modules,
		 * so they are registered lazily, when the migrator is created.
		 */
		return array( Version_Migrator::class => fn() => $factory->version_migrator() );
	}
}
