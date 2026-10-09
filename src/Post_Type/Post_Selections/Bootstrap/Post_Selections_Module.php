<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Post_Query\Core\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Query\Selection\Selection_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Post_Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Layout_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Table;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Post_Selection_Shortcode;

class Post_Selections_Module extends Module_Base {
	public static function get_type_definitions(): array {
		return array(
			Post_Query_Builder::class => Selection_Query_Builder::class,
		);
	}

	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Post_Selection_Settings::class     => fn() => $container->resolve( Post_Selections_Factory::class )
				->selection_settings(),
			Selection_Settings_Storage::class => fn() => $container->resolve( Post_Selections_Factory::class )
				->settings_storage(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Selection_Meta_Boxes::class,
			Post_Selections_Cpt::class,
			Post_Selections_Table::class,
			Selection_Save_Actions::class,
			Post_Selection_Shortcode::class,
			Selection_Git_Box::class,
			Selection_Layout_Integration::class,
			Selection_Interactive_Fields::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Post_Selections_Factory::class );

		// Generic (not specific to this CPT) hookables, created directly as the container can't host them per module.
		return array(
			Cpt_Gutenberg_Editor_Settings::class => fn() => $factory->editor_settings(),
			Cpt_Assets_Reducer::class            => fn() => $factory->assets_reducer(),
		);
	}
}
