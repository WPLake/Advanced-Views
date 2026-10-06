<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
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
	protected Cpt_Item_Picker $item_picker;
	protected Cpt_Renderer $cpt_renderer;

	public function get_hookables( Route_Detector $route_detector ): array {
		$layouts_settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$layout_cpt               = $this->resolve( Layouts_Cpt::class );

		$this->load_acf_groups( $route_detector, $layout_cpt );

		// instances are registered in the container right after creation (for types it can't autowire),
		// as they're used by the next ones and by other modules.
		$factory = $this->make_factory();
		$this->wire( Layout_Factory::class, $factory );

		$shortcodes      = $layout_cpt->shortcodes();
		$shortcode_block = new Shortcode_Gutenberg_Block( $shortcodes );
		$this->wire( Shortcode_Gutenberg_Block::class, $shortcode_block );

		$shortcode    = $this->resolve( Layout_Shortcode::class );
		$item_picker  = new Cpt_Item_Picker( $layouts_settings_storage, $layout_cpt );
		$cpt_renderer = new Cpt_Renderer( $shortcode, $layouts_settings_storage, $layout_cpt );

		$this->item_picker  = $item_picker;
		$this->cpt_renderer = $cpt_renderer;

		$asset_resolver = $this->resolve( Asset_Resolver::class );
		$cpt_block      = new Cpt_Gutenberg_Block( $cpt_renderer );
		$block          = new Layout_Gutenberg_Block( $asset_resolver, $item_picker, $cpt_block );

		$cpt_table           = $this->resolve( Layouts_Cpt_Table::class );
		$fs_only_tab         = new Fs_Only_Tab( $cpt_table, $layouts_settings_storage );
		$bulk_validation_tab = new Layouts_Bulk_Validation_Tab( $cpt_table, $layouts_settings_storage, $fs_only_tab, $factory );

		$pre_built_tab = $this->make_pre_built_tab();
		$this->wire( Layouts_Pre_Built_Tab::class, $pre_built_tab );

		$settings         = $this->resolve( Settings_Storage::class );
		$plugin           = $this->resolve( Plugin::class );
		$cpt_name         = $layout_cpt->cpt_name();
		$acf_integrations = $this->make_acf_integrations( $layout_cpt );

		$hookables = array(
			$this->resolve( Layout_Meta_Boxes::class ),
			new Layouts_Cpt_Hookable( $layout_cpt, $layouts_settings_storage ),
			$cpt_table,
			$fs_only_tab,
			$bulk_validation_tab,
			$pre_built_tab,
			new Cpt_Gutenberg_Editor_Settings( $cpt_name ),
			new Cpt_Assets_Reducer( $settings, $plugin, $cpt_name ),
			$this->resolve( Layout_Save_Actions::class ),
			$shortcode,
			$shortcode_block,
			$item_picker,
			$block,
			$this->resolve( Layout_Git_Box::class ),
			$this->resolve( Layout_Git_Tabs::class ),
			$this->resolve( Layout_Interactive_Fields::class ),
		);

		return array_merge( $acf_integrations, $hookables );
	}

	public function get_plugin_extensions(): array {
		return array(
			'elementor/loaded' => fn(): array => $this->create_elementor_hookables(),
		);
	}

	protected function load_acf_groups( Route_Detector $route_detector, Layouts_Cpt $layout_cpt ): void {
		$cpt_name     = $layout_cpt->cpt_name();
		$is_cpt_route = $route_detector->is_cpt_admin_route( $cpt_name );

		if ( wp_doing_ajax() || $is_cpt_route ) {
			$plugin      = $this->resolve( Plugin::class );
			$groups_path = $plugin->get_plugin_path( 'src/Post_Type/Layouts/Acf/Groups' );

			$groups = array(
				'Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups' => $groups_path,
			);

			Acf_Utils::load_groups( $groups );
		}
	}

	/**
	 * @return Hookable[]
	 */
	protected function make_acf_integrations( Layouts_Cpt $layout_cpt ): array {
		$cpt_name = $layout_cpt->cpt_name();

		return array(
			$this->resolve( Layout_Settings_Integration::class ),
			$this->resolve( Field_Settings_Integration::class ),
			$this->resolve( Item_Settings_Integration::class ),
			new Mount_Point_Settings_Integration( $cpt_name ),
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function create_elementor_hookables(): array {
		$widget_registrar = new Cpt_Widget_Registrar( $this->item_picker, $this->cpt_renderer );

		$widget_registrar->add_widget( Layout_Elementor_Widget::class );

		$asset_resolver = $this->resolve( Asset_Resolver::class );

		return array(
			$widget_registrar,
			new Layout_Elementor_Assets( $this->item_picker, $asset_resolver ),
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
