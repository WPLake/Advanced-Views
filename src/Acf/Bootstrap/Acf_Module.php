<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Module_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerInterface;
use function Org\Wplake\Advanced_Views\Utils\resolve;

class Acf_Module extends Module_Base {
	public static function get_hookable_factories( ContainerInterface $container ): array {
		return array(
			Acf_Groups_Loader::class => fn() => self::create_groups_loader( $container ),
		);
	}

	protected static function create_groups_loader( ContainerInterface $container ): Acf_Groups_Loader {
		$plugin        = resolve( $container, Plugin::class );
		$groups_path   = $plugin->get_plugin_path( 'src/Acf/Groups' );
		$namespace_map = array( 'Org\Wplake\Advanced_Views\Acf\Groups' => $groups_path );
		$cpt_names     = array(
			resolve( $container, Layouts_Cpt::class )->cpt_name(),
			resolve( $container, Selections_Cpt::class )->cpt_name(),
		);

		return new Acf_Groups_Loader( $namespace_map, $cpt_names );
	}
}
