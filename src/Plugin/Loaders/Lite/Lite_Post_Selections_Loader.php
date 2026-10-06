<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders\Lite;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Version_Migrator;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Post_Selections_Loader_Base;
use Org\Wplake\Advanced_Views\Post_Query\Selection\Selection_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Gutenberg\Cpt_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table\Layouts_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Post_Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Layout_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Table;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Post_Selection_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Elementor\Selection_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Elementor\Selection_Elementor_Widget;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Gutenberg\Selection_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Post_Selection_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Query;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Selection_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Selection_Markup;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;

final class Lite_Post_Selections_Loader extends Post_Selections_Loader_Base {
	public function __construct( Lite_Plugin_Loader $base ) {
		parent::__construct( $base->container );

		$layout_cpt    = $this->resolve( Layouts_Cpt::class );
		$selection_cpt = $this->resolve( Selections_Cpt::class );

		$query_builder         = $this->resolve( Selection_Query_Builder::class );
		$post_query            = $this->resolve( Post_Query::class );
		$post_selection_markup = new Post_Selection_Markup(
			$base->front_assets,
			$base->engines_storage,
			$layout_cpt
		);
		$this->factory         = new Post_Selection_Factory(
			$base->front_assets,
			PHP_Template_Engine::NAME,
			$post_query,
			$post_selection_markup,
			$base->engines_storage,
			$base->post_selections_settings_storage
		);
		$this->meta_boxes      = new Selection_Meta_Boxes(
			$base->html,
			$base->plugin,
			$base->post_selections_settings_storage,
			$base->layouts_settings_storage,
			$selection_cpt,
			$layout_cpt
		);
		$this->save_actions    = new Selection_Save_Actions(
			$base->logger,
			$base->post_selections_settings_storage,
			$base->plugin,
			$base->post_selection_settings,
			$base->front_assets,
			$post_selection_markup,
			$query_builder,
			$this->factory,
			$base->engines_storage
		);
		$this->wire( Selection_Save_Actions::class, $this->save_actions );

		$this->cpt                 = new Post_Selections_Cpt(
			$selection_cpt,
			$base->post_selections_settings_storage
		);
		$this->cpt_table           = new Post_Selections_Table(
			$base->post_selections_settings_storage,
			$selection_cpt,
			$base->html,
			$this->meta_boxes,
			$layout_cpt
		);
		$this->fs_only_tab         = new Fs_Only_Tab(
			$this->cpt_table,
			$base->post_selections_settings_storage
		);
		$this->bulk_validation_tab = new Post_Selections_Bulk_Validation_Tab(
			$this->cpt_table,
			$base->post_selections_settings_storage,
			$this->fs_only_tab,
			$this->factory
		);

		$file_system                      = new File_System(
			$base->logger,
			$selection_cpt->folder_name(),
			$base->plugin->get_plugin_path( 'pre_built' )
		);
		$db_management                    = new Db_Management(
			$base->logger,
			$file_system,
			$selection_cpt,
			true
		);
		$post_selections_settings_storage = new Selection_Settings_Storage(
			$base->logger,
			$file_system,
			new Post_Selection_Fs_Fields( $base->engines_storage ),
			$db_management,
			$base->post_selection_settings
		);
		$this->pre_built_tab              = new Post_Selections_Pre_Built_Tab(
			$this->cpt_table,
			$base->post_selections_settings_storage,
			$post_selections_settings_storage,
			$base->provider_cluster,
			$this->resolve( Version_Migrator::class ),
			$base->logger,
			$this->resolve( Layouts_Pre_Built_Tab::class )
		);

		$this->git_tabs = new Selection_Git_Tabs(
			$this->cpt_table,
			$base->settings,
			$base->git_lab_api,
			$base->group_creator->create( Post_Selection_Settings::class ),
			$base->post_selections_settings_storage,
			$this->resolve( Version_Migrator::class ),
			$this->resolve( Layout_Git_Tabs::class ),
			$base->provider_cluster,
			$base->logger
		);
		$this->git_box  = new Selection_Git_Box(
			$selection_cpt->cpt_name(),
			$base->settings,
			$base->post_selections_settings_storage,
			$base->git_lab_api,
			$base->layouts_settings_storage,
			$this->resolve( Layout_Git_Box::class ),
			$base->plugin
		);

		$this->cpt_assets_reducer            = new Cpt_Assets_Reducer(
			$base->settings,
			$base->plugin,
			$selection_cpt->cpt_name()
		);
		$this->cpt_gutenberg_editor_settings = new Cpt_Gutenberg_Editor_Settings(
			$selection_cpt->cpt_name()
		);

		$this->layout_integration = $this->resolve( Selection_Layout_Integration::class );
		$this->shortcode          = new Post_Selection_Shortcode(
			$selection_cpt,
			$base->settings,
			$base->post_selections_settings_storage,
			$base->live_reloader_component,
			$this->factory
		);
		$this->wire( Post_Selection_Shortcode::class, $this->shortcode );

		$this->item_picker = new Cpt_Item_Picker(
			$base->post_selections_settings_storage,
			$selection_cpt
		);

		$cpt_renderer = new Cpt_Renderer(
			$this->shortcode,
			$base->post_selections_settings_storage,
			$selection_cpt
		);

		$cpt_block = new Cpt_Gutenberg_Block( $cpt_renderer );

		$this->block = new Selection_Gutenberg_Block(
			$base->asset_resolver,
			$this->item_picker,
			$cpt_block
		);

		$this->make_elementor_integration = function () use ( $base, $cpt_renderer ): array {
			$integration = new Cpt_Widget_Registrar( $this->item_picker, $cpt_renderer );

			$integration->add_widget( Selection_Elementor_Widget::class );

			return array(
				$integration,
				new Selection_Elementor_Assets( $this->item_picker, $base->asset_resolver ),
			);
		};

		$this->interactive_fields = new Selection_Interactive_Fields(
			$selection_cpt,
			$base->html,
			$base->plugin,
			$base->post_selections_settings_storage,
			$post_selection_markup,
			$this->factory,
			$base->engines_storage,
			$base->provider_cluster,
			$base->settings,
			$this->meta_boxes,
			$base->layouts_settings_storage,
		);
	}
}
