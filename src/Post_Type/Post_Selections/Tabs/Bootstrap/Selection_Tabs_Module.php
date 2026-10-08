<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Selection_Git_Tabs;

class Selection_Tabs_Module extends Module_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Post_Selections_Pre_Built_Tab::class       => fn() =>
				$container->resolve( Selection_Tabs_Factory::class )
					->pre_built_tab(),
			Post_Selections_Bulk_Validation_Tab::class => fn() =>
				$container->resolve( Selection_Tabs_Factory::class )
					->bulk_validation_tab(),
		);
	}

	public static function get_hookable_classes(): array {
		return array(
			Post_Selections_Bulk_Validation_Tab::class,
			Post_Selections_Pre_Built_Tab::class,
			Selection_Git_Tabs::class,
		);
	}

	public static function get_hookable_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Selection_Tabs_Factory::class );

		return array( Fs_Only_Tab::class => fn() => $factory->fs_only_tab() );
	}
}
