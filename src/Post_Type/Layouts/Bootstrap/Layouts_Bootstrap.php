<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layouts_Cpt as Layouts_Cpt_Hookable;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table\Layouts_Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;

class Layouts_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookable_classes(): array {
		return array(
			Layout_Meta_Boxes::class,
			Layouts_Cpt_Hookable::class,
			Layouts_Cpt_Table::class,
			Layout_Save_Actions::class,
			Layout_Shortcode::class,
			Layout_Git_Box::class,
			Layout_Interactive_Fields::class,
		);
	}

	public function get_hookable_factories(): array {
		$factory = $this->resolve( Layouts_Factory::class );

		// Generic (not specific to this CPT) hookables, created directly as the container can't host them per module.
		return array(
			Cpt_Gutenberg_Editor_Settings::class => fn() => $factory->editor_settings(),
			Cpt_Assets_Reducer::class            => fn() => $factory->assets_reducer(),
		);
	}
}
