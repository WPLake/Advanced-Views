<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Repeater_Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Layout_Cpt as Layouts_Cpt_Hookable;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Table\Layout_Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Layout_Shortcode;

class Layout_Bootstrap extends Bootstrap_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Layout_Settings::class         => fn() => $container->resolve( Layout_Cpt_Factory::class )
				->layout_settings(),
			Item_Settings::class           => fn() => $container->resolve( Layout_Cpt_Factory::class )
				->item_settings(),
			Repeater_Field_Settings::class => fn() => $container->resolve( Layout_Cpt_Factory::class )
				->repeater_field_settings(),
			Layout_Settings_Storage::class => fn() => $container->resolve( Layout_Cpt_Factory::class )
				->settings_storage(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Layout_Meta_Boxes::class,
			Layouts_Cpt_Hookable::class,
			Layout_Cpt_Table::class,
			Layout_Save_Actions::class,
			Layout_Shortcode::class,
			Layout_Git_Box::class,
			Layout_Interactive_Fields::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Layout_Cpt_Factory::class );

		// Generic (not specific to this CPT) hookables, created directly as the container can't host them per module.
		return array(
			Cpt_Gutenberg_Editor_Settings::class => fn() => $factory->editor_settings(),
			Cpt_Assets_Reducer::class            => fn() => $factory->assets_reducer(),
			File_System::class                   => fn() => $container->resolve( Layout_Settings_Storage::class )
				->get_file_system(),
		);
	}
}
