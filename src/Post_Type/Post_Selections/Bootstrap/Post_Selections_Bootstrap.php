<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Query\Core\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Query\Selection\Selection_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Post_Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Layout_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Table;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Post_Selection_Shortcode;

class Post_Selections_Bootstrap extends Module_Bootstrap_Base {
	public static function get_definitions(): array {
		return array(
			Post_Query_Builder::class => Selection_Query_Builder::class,
		);
	}

	/**
	 * @return array<class-string<Hookable>>
	 */
	protected function get_hookable_classes(): array {
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

	/**
	 * @return Hookable[]
	 */
	protected function get_hookable_instances( Route_Detector $route_detector ): array {
		$factory = $this->resolve( Post_Selections_Factory::class );

		// Generic (not selection-specific) hookables, created directly as the container can't host them per module.
		return array(
			$factory->editor_settings(),
			$factory->assets_reducer(),
		);
	}
}
