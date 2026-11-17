<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Post_Query\Core\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Query\Selection\Selection_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Selection_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Selection_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Selection_Layout_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Selection_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Selection_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Table\Post_Selection_Table;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Integration\Post_Selection_Shortcode;

class Post_Selection_Bootstrap extends Bootstrap_Base {
	public static function get_type_definitions(): array {
		return array(
			Post_Query_Builder::class => Selection_Query_Builder::class,
		);
	}

	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Post_Selection_Settings::class    => fn() => $container->resolve( Post_Selection_Cpt_Factory::class )
				->selection_settings(),
			Selection_Settings_Storage::class => fn() => $container->resolve( Post_Selection_Cpt_Factory::class )
				->settings_storage(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Selection_Meta_Boxes::class,
			Post_Selection_Cpt::class,
			Post_Selection_Table::class,
			Selection_Save_Actions::class,
			Post_Selection_Shortcode::class,
			Selection_Git_Box::class,
			Selection_Layout_Integration::class,
			Selection_Interactive_Fields::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Post_Selection_Cpt_Factory::class );

		// Generic (not specific to this CPT) hookables, created directly as the container can't host them per module.
		return array(
			Cpt_Gutenberg_Editor_Settings::class => fn() => $factory->editor_settings(),
			Cpt_Assets_Reducer::class            => fn() => $factory->assets_reducer(),
			File_System::class                   => fn() => $container->resolve( Selection_Settings_Storage::class )
				->get_file_system(),
		);
	}
}
