<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layout_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Pre_Built_Tab;

class Layout_Tabs_Module extends Module_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Layouts_Pre_Built_Tab::class       => fn() =>
				$container->resolve( Layout_Tabs_Factory::class )
					->pre_built_tab(),
			Layouts_Bulk_Validation_Tab::class => fn() =>
				$container->resolve( Layout_Tabs_Factory::class )
					->bulk_validation_tab(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Layouts_Bulk_Validation_Tab::class,
			Layouts_Pre_Built_Tab::class,
			Layout_Git_Tabs::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Layout_Tabs_Factory::class );

		return array( Fs_Only_Tab::class => fn() => $factory->fs_only_tab() );
	}
}
