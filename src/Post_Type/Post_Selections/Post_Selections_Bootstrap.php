<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Query\Core\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Query\Selection\Selection_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Api_Interface;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Post_Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Layout_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Table;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Post_Selection_Shortcode;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;

class Post_Selections_Bootstrap extends Module_Bootstrap_Base {
	public function wire_instance_factories(): void {
		$wire_resolves = $this->get_wire_resolves();

		foreach ( $wire_resolves as $class_name => $factory ) {
			// fixme must return, no Wire.
			$this->wire( $class_name, $factory );
		}
	}

	public function get_hookables( Route_Detector $route_detector ): array {
		$hookable_classes = $this->get_hookable_classes();
		$resolved         = array_map(
			fn( string $class_name ): Hookable => $this->resolve( $class_name ),
			$hookable_classes
		);

		$instances = $this->get_instances();

		return array_merge( $resolved, $instances );
	}

	/**
	 * @return array<class-string, \Closure>
	 */
	protected function get_wire_resolves(): array {
		return array(
			Post_Selection_Factory::class => fn(): Post_Selection_Factory => $this->make_factory(),
			Post_Query_Builder::class     => fn(): Post_Query_Builder => $this->resolve( Selection_Query_Builder::class ),
			Selection_Git_Box::class      => fn(): Selection_Git_Box => $this->make_git_box(),
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
	 * Generic (not selection-specific) hookables, created directly as the container can't host them per module
	 *
	 * @return Hookable[]
	 */
	protected function get_instances(): array {
		return array(
			$this->create_editor_settings(),
			$this->create_assets_reducer(),
		);
	}

	protected function create_editor_settings(): Cpt_Gutenberg_Editor_Settings {
		$selections_cpt = $this->resolve( Selections_Cpt::class );
		$cpt_name       = $selections_cpt->cpt_name();

		return new Cpt_Gutenberg_Editor_Settings( $cpt_name );
	}

	protected function create_assets_reducer(): Cpt_Assets_Reducer {
		$settings_storage = $this->resolve( Settings_Storage::class );
		$plugin           = $this->resolve( Plugin::class );
		$selections_cpt   = $this->resolve( Selections_Cpt::class );
		$cpt_name         = $selections_cpt->cpt_name();

		return new Cpt_Assets_Reducer( $settings_storage, $plugin, $cpt_name );
	}

	protected function make_git_box(): Selection_Git_Box {
		$selections_cpt   = $this->resolve( Selections_Cpt::class );
		$settings         = $this->resolve( Settings_Storage::class );
		$settings_storage = $this->resolve( Selection_Settings_Storage::class );
		$git_api          = $this->resolve( Git_Api_Interface::class );
		$layouts_storage  = $this->resolve( Layout_Settings_Storage::class );
		$layouts_git_box  = $this->resolve( Layout_Git_Box::class );
		$plugin           = $this->resolve( Plugin::class );

		return new Selection_Git_Box(
			$selections_cpt,
			$settings,
			$settings_storage,
			$git_api,
			$layouts_storage,
			$layouts_git_box,
			$plugin
		);
	}

	protected function make_factory(): Post_Selection_Factory {
		$front_assets     = $this->resolve( Front_Assets::class );
		$post_query       = $this->resolve( Post_Query::class );
		$markup           = $this->resolve( Post_Selection_Markup::class );
		$renderer_storage = $this->resolve( Template_Renderer_Storage::class );
		$settings_storage = $this->resolve( Selection_Settings_Storage::class );

		return new Post_Selection_Factory(
			$front_assets,
			PHP_Template_Engine::NAME,
			$post_query,
			$markup,
			$renderer_storage,
			$settings_storage
		);
	}
}
