<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Acf_Module extends Module_Base {
	public static function get_actor_factories( Instance_Container $container ): array {
		return array(
			Acf_Groups_Loader::class => fn() => self::create_groups_loader( $container ),
		);
	}

	protected static function create_groups_loader( Instance_Container $container ): Acf_Groups_Loader {
		$plugin        = $container->resolve( Plugin::class );
		$groups_path   = $plugin->get_plugin_path( 'src/Acf/Groups' );
		$namespace_map = array( 'Org\Wplake\Advanced_Views\Acf\Groups' => $groups_path );
		$cpt_names     = array(
			$container->resolve( Layouts_Cpt::class )->cpt_name(),
			$container->resolve( Selections_Cpt::class )->cpt_name(),
		);

		return new Acf_Groups_Loader( $namespace_map, $cpt_names );
	}
}
