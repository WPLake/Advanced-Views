<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Groups_Loader;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Version_Migrator;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Gutenberg\Cpt_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Item_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Layout_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layouts_Cpt as Layouts_Cpt_Hookable;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table\Layouts_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table\Layouts_Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table\Layouts_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Fields\Field_Markup;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\Layout_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\Layout_Elementor_Widget;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Layout_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;

class Layouts_Bootstrap extends Module_Bootstrap_Base {
	public function wire_factories(): void {
		foreach ( $this->get_wire_resolves() as $class_name => $factory ) {
			$this->wire( $class_name, $factory );
		}
	}

	public function get_hookables( Route_Detector $route_detector ): array {
		$resolved = array_map(
			fn( string $class_name ): Hookable => $this->resolve( $class_name ),
			$this->get_hookable_classes()
		);

		return array_merge( $this->get_acf_groups_hookables( $route_detector ), $resolved, $this->get_instances() );
	}

	public function get_extension_hookables(): array {
		return array(
			'elementor/loaded' => fn(): array => $this->create_elementor_hookables(),
		);
	}

	/**
	 * @return array<class-string<Hookable>>
	 */
	protected function get_hookable_classes(): array {
		return array(
			Layout_Settings_Integration::class,
			Field_Settings_Integration::class,
			Item_Settings_Integration::class,
			Layout_Meta_Boxes::class,
			Layouts_Cpt_Hookable::class,
			Layouts_Cpt_Table::class,
			Layouts_Bulk_Validation_Tab::class,
			Layouts_Pre_Built_Tab::class,
			Layout_Save_Actions::class,
			Layout_Shortcode::class,
			Layout_Gutenberg_Block::class,
			Layout_Git_Box::class,
			Layout_Git_Tabs::class,
			Layout_Interactive_Fields::class,
		);
	}

	/**
	 * Generic (not layout-specific) hookables, created directly as the container can't host them per module
	 *
	 * @return Hookable[]
	 */
	protected function get_instances(): array {
		return array(
			$this->create_item_picker(),
			$this->create_shortcode_block(),
			$this->create_fs_only_tab(),
			$this->create_editor_settings(),
			$this->create_assets_reducer(),
			$this->create_mount_point_integration(),
		);
	}

	/**
	 * @return array<class-string, callable>
	 */
	protected function get_wire_resolves(): array {
		return array(
			Layout_Factory::class              => fn(): Layout_Factory => $this->make_factory(),
			Layouts_Pre_Built_Tab::class       => fn(): Layouts_Pre_Built_Tab => $this->make_pre_built_tab(),
			Layouts_Cpt_Hookable::class        => fn(): Layouts_Cpt_Hookable => $this->make_cpt_hookable(),
			Layouts_Bulk_Validation_Tab::class => fn(): Layouts_Bulk_Validation_Tab => $this->make_bulk_validation_tab(),
			Layout_Gutenberg_Block::class      => fn(): Layout_Gutenberg_Block => $this->make_gutenberg_block(),
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function get_acf_groups_hookables( Route_Detector $route_detector ): array {
		$cpt_name = $this->resolve( Layouts_Cpt::class )->cpt_name();

		if ( ! wp_doing_ajax() && ! $route_detector->is_cpt_admin_route( $cpt_name ) ) {
			return array();
		}

		$groups_path = $this->resolve( Plugin::class )->get_plugin_path( 'src/Post_Type/Layouts/Acf/Groups' );

		return array(
			new Acf_Groups_Loader(
				array( 'Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups' => $groups_path )
			),
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function create_elementor_hookables(): array {
		$item_picker      = $this->create_item_picker();
		$widget_registrar = new Cpt_Widget_Registrar( $item_picker, $this->create_cpt_renderer() );

		$widget_registrar->add_widget( Layout_Elementor_Widget::class );

		return array(
			$widget_registrar,
			new Layout_Elementor_Assets( $item_picker, $this->resolve( Asset_Resolver::class ) ),
		);
	}

	protected function make_cpt_hookable(): Layouts_Cpt_Hookable {
		return new Layouts_Cpt_Hookable(
			$this->resolve( Layouts_Cpt::class ),
			$this->resolve( Layout_Settings_Storage::class )
		);
	}

	protected function create_fs_only_tab(): Fs_Only_Tab {
		return new Fs_Only_Tab(
			$this->resolve( Layouts_Cpt_Table::class ),
			$this->resolve( Layout_Settings_Storage::class )
		);
	}

	protected function make_bulk_validation_tab(): Layouts_Bulk_Validation_Tab {
		return new Layouts_Bulk_Validation_Tab(
			$this->resolve( Layouts_Cpt_Table::class ),
			$this->resolve( Layout_Settings_Storage::class ),
			$this->create_fs_only_tab(),
			$this->resolve( Layout_Factory::class )
		);
	}

	protected function create_editor_settings(): Cpt_Gutenberg_Editor_Settings {
		return new Cpt_Gutenberg_Editor_Settings( $this->resolve( Layouts_Cpt::class )->cpt_name() );
	}

	protected function create_assets_reducer(): Cpt_Assets_Reducer {
		return new Cpt_Assets_Reducer(
			$this->resolve( Settings_Storage::class ),
			$this->resolve( Plugin::class ),
			$this->resolve( Layouts_Cpt::class )->cpt_name()
		);
	}

	protected function create_mount_point_integration(): Mount_Point_Settings_Integration {
		return new Mount_Point_Settings_Integration( $this->resolve( Layouts_Cpt::class )->cpt_name() );
	}

	protected function create_shortcode_block(): Shortcode_Gutenberg_Block {
		return new Shortcode_Gutenberg_Block( $this->resolve( Layouts_Cpt::class )->shortcodes() );
	}

	protected function create_item_picker(): Cpt_Item_Picker {
		return new Cpt_Item_Picker(
			$this->resolve( Layout_Settings_Storage::class ),
			$this->resolve( Layouts_Cpt::class )
		);
	}

	protected function create_cpt_renderer(): Cpt_Renderer {
		return new Cpt_Renderer(
			$this->resolve( Layout_Shortcode::class ),
			$this->resolve( Layout_Settings_Storage::class ),
			$this->resolve( Layouts_Cpt::class )
		);
	}

	protected function make_gutenberg_block(): Layout_Gutenberg_Block {
		return new Layout_Gutenberg_Block(
			$this->resolve( Asset_Resolver::class ),
			$this->create_item_picker(),
			new Cpt_Gutenberg_Block( $this->create_cpt_renderer() )
		);
	}

	protected function make_factory(): Layout_Factory {
		$front_assets     = $this->resolve( Front_Assets::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$markup           = $this->resolve( Layout_Markup::class );
		$renderer_storage = $this->resolve( Template_Renderer_Storage::class );
		$field_markup     = $this->resolve( Field_Markup::class );
		$provider_cluster = $this->resolve( Field_Provider_Cluster::class );

		return new Layout_Factory(
			$front_assets,
			PHP_Template_Engine::NAME,
			$settings_storage,
			$markup,
			$renderer_storage,
			$field_markup,
			$provider_cluster
		);
	}

	protected function make_pre_built_tab(): Layouts_Pre_Built_Tab {
		$logger           = $this->resolve( Logger::class );
		$layout_cpt       = $this->resolve( Layouts_Cpt::class );
		$plugin           = $this->resolve( Plugin::class );
		$engines_storage  = $this->resolve( Engines_Storage::class );
		$layout_settings  = $this->resolve( Layout_Settings::class );
		$provider_cluster = $this->resolve( Field_Provider_Cluster::class );

		$folder_name    = $layout_cpt->folder_name();
		$pre_built_path = $plugin->get_plugin_path( 'pre_built' );
		$file_system    = new File_System( $logger, $folder_name, $pre_built_path );
		$fs_fields      = new Layout_Fs_Fields( $engines_storage );
		$db_management  = new Db_Management( $logger, $file_system, $layout_cpt, true );

		$pre_built_settings_storage = new Layout_Settings_Storage(
			$logger,
			$file_system,
			$fs_fields,
			$db_management,
			$layout_settings
		);

		$cpt_table        = $this->resolve( Layouts_Cpt_Table::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$migrator         = $this->resolve( Version_Migrator::class );

		return new Layouts_Pre_Built_Tab(
			$cpt_table,
			$settings_storage,
			$pre_built_settings_storage,
			$provider_cluster,
			$migrator,
			$logger
		);
	}
}
