<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Version_Migrator;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html_Printer;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Gutenberg\Cpt_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layouts_Cpt;
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
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;

class Layouts_Bootstrap extends Module_Bootstrap_Base {
	protected Public_Cpt $layout_cpt;
	protected Public_Cpt $post_selection_cpt;
	protected Cpt_Item_Picker $item_picker;
	protected Cpt_Renderer $cpt_renderer;

	public function __construct( Container $container, Public_Cpt $layout_cpt, Public_Cpt $post_selection_cpt ) {
		parent::__construct( $container );

		$this->layout_cpt         = $layout_cpt;
		$this->post_selection_cpt = $post_selection_cpt;
	}

	public function get_hookables( Route_Detector $route_detector ): array {
		// fixme find a way to class look clearer.
		// a) add ->get/set as short $this->container alias (on the _Base level)
		// b) split into groups, then this method that merges them.
		$layouts_settings_storage = $this->container->get( Layout_Settings_Storage::class );

		// instances are registered in the container right after creation (for types it can't autowire),
		// as they're used by the next ones and by other modules.
		$factory = $this->make_factory();
		$this->container->set( Layout_Factory::class, $factory );

		$meta_boxes = $this->make_meta_boxes();
		$this->container->set( Layout_Meta_Boxes::class, $meta_boxes );

		$save_actions = $this->make_save_actions();
		$this->container->set( Layout_Save_Actions::class, $save_actions );

		$shortcode_block = new Shortcode_Gutenberg_Block( $this->layout_cpt->shortcodes() );
		$shortcode       = new Layout_Shortcode(
			$this->layout_cpt,
			$this->container->get( Settings_Storage::class ),
			$layouts_settings_storage,
			$this->container->get( Live_Reloader_Component::class ),
			$factory,
			$shortcode_block
		);
		$this->container->set( Layout_Shortcode::class, $shortcode );

		$item_picker        = new Cpt_Item_Picker( $layouts_settings_storage, $this->layout_cpt );
		$cpt_renderer       = new Cpt_Renderer( $shortcode, $layouts_settings_storage, $this->layout_cpt );
		$this->item_picker  = $item_picker;
		$this->cpt_renderer = $cpt_renderer;
		$block              = new Layout_Gutenberg_Block(
			$this->container->get( Asset_Resolver::class ),
			$item_picker,
			new Cpt_Gutenberg_Block( $cpt_renderer )
		);

		$cpt_table = $this->make_cpt_table();
		$this->container->set( Layouts_Cpt_Table::class, $cpt_table );

		$fs_only_tab         = new Fs_Only_Tab( $cpt_table, $layouts_settings_storage );
		$bulk_validation_tab = new Layouts_Bulk_Validation_Tab( $cpt_table, $layouts_settings_storage, $fs_only_tab, $factory );

		$pre_built_tab = $this->make_pre_built_tab();
		$this->container->set( Layouts_Pre_Built_Tab::class, $pre_built_tab );

		$git_tabs = $this->make_git_tabs();
		$this->container->set( Layout_Git_Tabs::class, $git_tabs );

		$git_box = $this->make_git_box();
		$this->container->set( Layout_Git_Box::class, $git_box );

		$interactive_fields = $this->make_interactive_fields();
		$this->container->set( Layout_Interactive_Fields::class, $interactive_fields );

		return array(
			$meta_boxes,
			new Layouts_Cpt( $this->layout_cpt, $layouts_settings_storage ),
			$cpt_table,
			$fs_only_tab,
			$bulk_validation_tab,
			$pre_built_tab,
			new Cpt_Gutenberg_Editor_Settings( $this->layout_cpt->cpt_name() ),
			new Cpt_Assets_Reducer(
				$this->container->get( Settings_Storage::class ),
				$this->container->get( Plugin::class ),
				$this->layout_cpt->cpt_name()
			),
			$save_actions,
			$shortcode,
			$shortcode_block,
			$item_picker,
			$block,
			$git_box,
			$git_tabs,
			$interactive_fields,
		);
	}

	public function get_plugin_extensions(): array {
		return array(
			'elementor/loaded' => fn(): array => $this->create_elementor_hookables(),
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function create_elementor_hookables(): array {
		$widget_registrar = new Cpt_Widget_Registrar( $this->item_picker, $this->cpt_renderer );

		$widget_registrar->add_widget( Layout_Elementor_Widget::class );

		return array(
			$widget_registrar,
			new Layout_Elementor_Assets( $this->item_picker, $this->container->get( Asset_Resolver::class ) ),
		);
	}

	protected function make_factory(): Layout_Factory {
		return new Layout_Factory(
			$this->container->get( Front_Assets::class ),
			PHP_Template_Engine::NAME,
			$this->container->get( Layout_Settings_Storage::class ),
			$this->container->get( Layout_Markup::class ),
			$this->container->get( Template_Renderer_Storage::class ),
			$this->container->get( Field_Markup::class ),
			$this->container->get( Field_Provider_Cluster::class )
		);
	}

	protected function make_meta_boxes(): Layout_Meta_Boxes {
		return new Layout_Meta_Boxes(
			$this->container->get( Html_Printer::class ),
			$this->container->get( Plugin::class ),
			$this->container->get( Layout_Settings_Storage::class ),
			$this->container->get( Field_Provider_Cluster::class ),
			$this->layout_cpt,
			$this->post_selection_cpt
		);
	}

	protected function make_save_actions(): Layout_Save_Actions {
		return new Layout_Save_Actions(
			$this->container->get( Logger::class ),
			$this->container->get( Layout_Settings_Storage::class ),
			$this->container->get( Plugin::class ),
			$this->container->get( Layout_Settings::class ),
			$this->container->get( Front_Assets::class ),
			$this->container->get( Layout_Markup::class ),
			$this->container->get( Layout_Factory::class ),
			$this->container->get( Template_Integration_Storage::class )
		);
	}

	protected function make_cpt_table(): Layouts_Cpt_Table {
		return new Layouts_Cpt_Table(
			$this->container->get( Layout_Settings_Storage::class ),
			$this->layout_cpt,
			$this->container->get( Html_Printer::class ),
			$this->container->get( Layout_Meta_Boxes::class ),
			$this->post_selection_cpt
		);
	}

	protected function make_pre_built_tab(): Layouts_Pre_Built_Tab {
		$logger = $this->container->get( Logger::class );

		$file_system                = new File_System(
			$logger,
			$this->layout_cpt->folder_name(),
			$this->container->get( Plugin::class )->get_plugin_path( 'pre_built' )
		);
		$pre_built_settings_storage = new Layout_Settings_Storage(
			$logger,
			$file_system,
			new Layout_Fs_Fields( $this->container->get( Engines_Storage::class ) ),
			new Db_Management( $logger, $file_system, $this->layout_cpt, true ),
			$this->container->get( Layout_Settings::class )
		);

		return new Layouts_Pre_Built_Tab(
			$this->container->get( Layouts_Cpt_Table::class ),
			$this->container->get( Layout_Settings_Storage::class ),
			$pre_built_settings_storage,
			$this->container->get( Field_Provider_Cluster::class ),
			$this->container->get( Version_Migrator::class ),
			$logger
		);
	}

	protected function make_git_tabs(): Layout_Git_Tabs {
		return new Layout_Git_Tabs(
			$this->container->get( Layouts_Cpt_Table::class ),
			$this->container->get( Settings_Storage::class ),
			$this->container->get( Git_Lab_Api::class ),
			$this->container->get( Creator::class )->create( Layout_Settings::class ),
			$this->container->get( Layout_Settings_Storage::class ),
			$this->container->get( Version_Migrator::class ),
			$this->container->get( Field_Provider_Cluster::class ),
			$this->container->get( Logger::class )
		);
	}

	protected function make_git_box(): Layout_Git_Box {
		return new Layout_Git_Box(
			$this->layout_cpt->cpt_name(),
			$this->container->get( Settings_Storage::class ),
			$this->container->get( Layout_Settings_Storage::class ),
			$this->container->get( Git_Lab_Api::class ),
			$this->container->get( Field_Provider_Cluster::class ),
			$this->container->get( Plugin::class )
		);
	}

	protected function make_interactive_fields(): Layout_Interactive_Fields {
		return new Layout_Interactive_Fields(
			$this->layout_cpt,
			$this->container->get( Html_Printer::class ),
			$this->container->get( Plugin::class ),
			$this->container->get( Layout_Settings_Storage::class ),
			$this->container->get( Layout_Factory::class ),
			$this->container->get( Template_Integration_Storage::class ),
			$this->container->get( Field_Provider_Cluster::class ),
			$this->container->get( Settings_Storage::class ),
			$this->container->get( Layout_Markup::class ),
			$this->container->get( Layout_Meta_Boxes::class )
		);
	}
}
