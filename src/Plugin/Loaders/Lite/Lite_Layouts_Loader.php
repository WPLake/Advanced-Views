<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders\Lite;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Layouts_Loader_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Core\Integrations\Cpt_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Core\Integrations\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Core\Integrations\Cpt_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Integrations\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Layout_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Table\Layouts_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Table\Layouts_Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Table\Layouts_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Data_Storage\Layout_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Fields\Field_Markup;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Integrations\Elementor\Layout_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Integrations\Elementor\Layout_Elementor_Widget;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Integrations\Gutenberg\Layout_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Integrations\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Integrations\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Layout_Markup;
use Org\Wplake\Advanced_Views\Template_Engine\Engines\PHP\PHP_Template_Engine;

final class Lite_Layouts_Loader extends Layouts_Loader_Base {
	public function __construct( Lite_Plugin_Loader $base ) {
		parent::__construct();

		$field_markup  = new Field_Markup(
			$base->provider_cluster,
			$base->front_assets,
			$base->engines_storage
		);
		$layout_markup = new Layout_Markup(
			$field_markup,
			$base->provider_cluster,
			$base->engines_storage
		);

		$this->factory         = new Layout_Factory(
			$base->front_assets,
			PHP_Template_Engine::NAME,
			$base->layouts_settings_storage,
			$layout_markup,
			$base->engines_storage,
			$field_markup,
			$base->provider_cluster
		);
		$this->cpt_meta_boxes  = new Layout_Meta_Boxes(
			$base->html,
			$base->plugin,
			$base->layouts_settings_storage,
			$base->provider_cluster,
			$base->layout_cpt,
			$base->post_selection_cpt
		);
		$this->shortcode_block = new Shortcode_Gutenberg_Block( $base->layout_cpt->shortcodes() );

		$this->save_actions = new Layout_Save_Actions(
			$base->logger,
			$base->layouts_settings_storage,
			$base->plugin,
			$base->layout_settings,
			$base->front_assets,
			$layout_markup,
			$this->factory,
			$base->layout_cpt,
			$base->engines_storage
		);

		$this->shortcode = new Layout_Shortcode(
			$base->layout_cpt,
			$base->settings,
			$base->layouts_settings_storage,
			$base->front_assets,
			$base->live_reloader_component,
			$this->factory,
			$this->shortcode_block
		);

		$this->item_picker = new Cpt_Item_Picker(
			$base->layouts_settings_storage,
			$base->layout_cpt
		);

		$cpt_renderer = new Cpt_Renderer(
			$this->shortcode,
			$base->front_assets,
			$base->layouts_settings_storage,
			$base->layout_cpt
		);

		$cpt_block = new Cpt_Gutenberg_Block( $cpt_renderer );

		$this->block = new Layout_Gutenberg_Block(
			$base->plugin,
			$this->item_picker,
			$cpt_block
		);

		$this->create_elementor_integration = function () use ( $base, $cpt_renderer ): array {
			$widget_registrar = new Cpt_Widget_Registrar( $this->item_picker, $cpt_renderer );

			$widget_registrar->add_widget( new Layout_Elementor_Widget() );

			return array(
				$widget_registrar,
				new Layout_Elementor_Assets( $this->item_picker, $base->plugin ),
			);
		};

		$this->cpt                 = new Layouts_Cpt( $base->layout_cpt, $base->layouts_settings_storage );
		$this->cpt_table           = new Layouts_Cpt_Table(
			$base->layouts_settings_storage,
			$base->layout_cpt,
			$base->html,
			$this->cpt_meta_boxes,
			$base->post_selection_cpt
		);
		$this->fs_only_tab         = new Fs_Only_Tab( $this->cpt_table, $base->layouts_settings_storage );
		$this->bulk_validation_tab = new Layouts_Bulk_Validation_Tab(
			$this->cpt_table,
			$base->layouts_settings_storage,
			$this->fs_only_tab,
			$this->factory
		);

		$file_system              = new File_System(
			$base->logger,
			$base->layout_cpt->folder_name(),
			$base->plugin->get_plugin_path( 'pre_built' )
		);
		$db_management            = new Db_Management(
			$base->logger,
			$file_system,
			$base->layout_cpt,
			true
		);
		$layouts_settings_storage = new Layout_Settings_Storage(
			$base->logger,
			$file_system,
			new Layout_Fs_Fields( $base->engines_storage ),
			$db_management,
			$base->layout_settings
		);
		$this->pre_built_tab      = new Layouts_Pre_Built_Tab(
			$this->cpt_table,
			$base->layouts_settings_storage,
			$layouts_settings_storage,
			$base->provider_cluster,
			$base->version_migrator,
			$base->logger
		);

		$this->cpt_assets_reducer            = new Cpt_Assets_Reducer(
			$base->settings,
			$base->plugin,
			$base->layout_cpt->cpt_name()
		);
		$this->cpt_gutenberg_editor_settings = new Cpt_Gutenberg_Editor_Settings( $base->layout_cpt->cpt_name() );

		$this->git_tabs = new Layout_Git_Tabs(
			$this->cpt_table,
			$base->settings,
			$base->git_lab_api,
			$base->group_creator->create( Layout_Settings::class ),
			$base->layouts_settings_storage,
			$base->version_migrator,
			$base->provider_cluster,
			$base->logger
		);
		$this->git_box  = new Layout_Git_Box(
			$base->layout_cpt->cpt_name(),
			$base->settings,
			$base->layouts_settings_storage,
			$base->git_lab_api,
			$base->provider_cluster,
			$base->plugin
		);

		$this->interactive_fields = new Layout_Interactive_Fields(
			$base->layout_cpt,
			$base->html,
			$base->plugin,
			$base->layouts_settings_storage,
			$this->factory,
			$base->engines_storage,
			$base->provider_cluster,
			$base->settings,
			$layout_markup,
			$this->cpt_meta_boxes
		);
	}
}
