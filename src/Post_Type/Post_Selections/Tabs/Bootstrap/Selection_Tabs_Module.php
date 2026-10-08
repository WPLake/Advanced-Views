<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Module_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Selection_Git_Tabs;
use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerInterface;
use function Org\Wplake\Advanced_Views\Utils\resolve;

class Selection_Tabs_Module extends Module_Base {
	public static function get_instance_factories( ContainerInterface $container ): array {
		return array(
			Post_Selections_Pre_Built_Tab::class       => fn() =>
				resolve( $container, Selection_Tabs_Factory::class )
					->pre_built_tab(),
			Post_Selections_Bulk_Validation_Tab::class => fn() =>
				resolve( $container, Selection_Tabs_Factory::class )
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

	public static function get_hookable_factories( ContainerInterface $container ): array {
		$factory = resolve( $container, Selection_Tabs_Factory::class );

		return array( Fs_Only_Tab::class => fn() => $factory->fs_only_tab() );
	}
}
