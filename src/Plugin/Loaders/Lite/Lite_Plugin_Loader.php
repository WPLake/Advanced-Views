<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders\Lite;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Dependency;
use Org\Wplake\Advanced_Views\Acf\Acf_Internal_Features;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Custom_Acf_Field_Types;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Item_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Layout_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Meta_Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Post_Selection_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Tax_Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Tools_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Groups\Git_Repository;
use Org\Wplake\Advanced_Views\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Plugin_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Tools_Settings;
use Org\Wplake\Advanced_Views\Assets\Admin_Assets;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Upgrade_Notice;
use Org\Wplake\Advanced_Views\Field_Provider\Data_Vendors;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\State_Report;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\Usage_Report;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Bar;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Pages;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html_Printer;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Debug_Dump_Creator;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Demo_Importer;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools_Page;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Plugin_Loader_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Environment;
use Org\Wplake\Advanced_Views\Plugin\Settings\Options_Storage;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Page;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\Cache_Flusher;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Post_Selection_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Blade\Blade_Template_Engine;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Templates_Environment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Twig\Twig_Template_Engine;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;

final class Lite_Plugin_Loader extends Plugin_Loader_Base {
	public Html_Printer $html;
	public Layout_Settings $layout_settings;
	public Post_Selection_Settings $post_selection_settings;
	public Options_Storage $options;

	public string $plugin_file;

	public function __construct( string $plugin_file ) {
		parent::__construct();

		$this->plugin_file = $plugin_file;
	}

	/**
	 * @return Hookable[]
	 */
	protected function primary(): array {
		$this->layout_cpt         = self::make_layout_cpt();
		$this->post_selection_cpt = Lite_Post_Selections_Loader::make_post_selection_cpt();

		$this->plugin_cpts = array(
			$this->layout_cpt,
			$this->post_selection_cpt,
		);

		$this->options  = new Options_Storage();
		$this->settings = new Settings_Storage( $this->options );
		$this->container->set( Settings_Storage::class, $this->settings );

		$uploads_folder = self::uploads_folder();
		$this->logger   = new Logger( $uploads_folder, $this->settings );
		$this->container->set( Logger::class, $this->logger );

		$this->group_creator           = new Creator();
		$this->layout_settings         = $this->group_creator->create( Layout_Settings::class );
		$this->post_selection_settings = $this->group_creator->create( Post_Selection_Settings::class );

		$this->html            = new Html_Printer();
		$twig_engine           = new Twig_Template_Engine( $uploads_folder, $this->logger, $this->settings );
		$this->engines_storage = new Engines_Storage(
			array(
				$twig_engine,
				new Blade_Template_Engine( $uploads_folder, $this->logger, $this->settings ),
				new PHP_Template_Engine( $this->logger, $this->settings ),
			),
			$twig_engine
		);

		$post_selections_file_system            = new File_System(
			$this->logger,
			$this->post_selection_cpt->folder_name()
		);
		$this->post_selections_settings_storage = new Selection_Settings_Storage(
			$this->logger,
			$post_selections_file_system,
			new Post_Selection_Fs_Fields( $this->engines_storage ),
			new Db_Management( $this->logger, $post_selections_file_system, $this->post_selection_cpt ),
			$this->post_selection_settings
		);
		$this->container->set( Selection_Settings_Storage::class, $this->post_selections_settings_storage );

		$layouts_file_system            = new File_System( $this->logger, $this->layout_cpt->folder_name() );
		$this->layouts_settings_storage = new Layout_Settings_Storage(
			$this->logger,
			$layouts_file_system,
			new Layout_Fs_Fields( $this->engines_storage ),
			new Db_Management( $this->logger, $layouts_file_system, $this->layout_cpt ),
			$this->layout_settings
		);
		$this->container->set( Layout_Settings_Storage::class, $this->layouts_settings_storage );

		$this->plugin                = new Plugin( $this->plugin_file, $this->options, $this->settings );
		$this->container->set( Plugin::class, $this->plugin );
		$this->asset_resolver        = new Asset_Resolver( $this->plugin_file, $this->plugin->get_version() );
		$this->templates_environment = new Templates_Environment(
			$uploads_folder,
			$this->logger,
			$this->plugin,
		);
		$this->container->set( Templates_Environment::class, $this->templates_environment );

		$this->item_settings = $this->group_creator->create( Item_Settings::class );

		$this->provider_cluster        = new Data_Vendors( $this->logger );
		$this->live_reloader_component = new Live_Reloader_Component( $this->asset_resolver, $this->settings );
		$this->front_assets            = new Front_Assets(
			$this->asset_resolver,
			$layouts_file_system,
			$this->provider_cluster,
			$this->live_reloader_component
		);
		$this->git_lab_api             = new Git_Lab_Api(
			$this->logger,
			$this->options,
			$this->layout_cpt,
			$this->post_selection_cpt
		);
		$this->upgrade_notice          = new Upgrade_Notice( $this->plugin );
		$this->cache_flusher           = new Cache_Flusher( $this->logger, $this->get_cache_cleaners() );
		$this->container->set( Cache_Flusher::class, $this->cache_flusher );

		$this->add_file_systems(
			array(
				$layouts_file_system,
				$post_selections_file_system,
			)
		);

		return parent::primary();
	}

	/**
	 * @return Hookable[]
	 */
	protected function layouts(): array {
		$this->layouts_loader = new Lite_Layouts_Loader( $this );

		return parent::layouts();
	}

	/**
	 * @return Hookable[]
	 */
	protected function post_selections(): array {
		$this->selections_loader = new Lite_Post_Selections_Loader( $this );

		return parent::post_selections();
	}

	/**
	 * @return Hookable[]
	 */
	protected function integration( Route_Detector $route_detector ): array {
		$this->acf_dependency = new Acf_Dependency( $this->asset_resolver );

		$this->layout_settings_integration         = new Layout_Settings_Integration(
			$this->layout_cpt->cpt_name(),
			$this->provider_cluster,
			$this->engines_storage
		);
		$this->field_settings_integration          = new Field_Settings_Integration(
			$this->provider_cluster,
			$this->layout_cpt
		);
		$this->post_selection_settings_integration = new Post_Selection_Settings_Integration(
			$this->post_selection_cpt->cpt_name(),
			$this->provider_cluster,
			$this->layout_cpt,
			$this->engines_storage
		);
		$this->item_settings_integration           = new Item_Settings_Integration(
			$this->layout_cpt->cpt_name(),
			$this->provider_cluster
		);
		// metaField is a part of the Meta Filter, so we use 'cardsCpt' here.
		$this->meta_field_settings_integration        = new Meta_Field_Settings_Integration(
			$this->post_selection_cpt->cpt_name(),
			$this->provider_cluster,
			$this->plugin
		);
		$this->layout_mount_point_integration         = new Mount_Point_Settings_Integration(
			$this->layout_cpt->cpt_name()
		);
		$this->post_selection_mount_point_integration = new Mount_Point_Settings_Integration(
			$this->post_selection_cpt->cpt_name()
		);
		$this->tax_field_settings_integration         = new Tax_Field_Settings_Integration(
			$this->post_selection_cpt->cpt_name(),
			$this->provider_cluster,
			$this->plugin
		);
		$this->tools_settings_integration             = new Tools_Settings_Integration(
			$this->layouts_settings_storage,
			$this->post_selections_settings_storage
		);
		$this->custom_acf_field_types                 = new Custom_Acf_Field_Types( $this->layouts_settings_storage );

		return parent::integration( $route_detector );
	}

	/**
	 * @return Hookable[]
	 */
	protected function others(): array {
		$this->demo_import = new Demo_Importer(
			$this->selections_loader->save_actions,
			$this->layouts_loader->save_actions,
			$this->post_selections_settings_storage,
			$this->layouts_settings_storage,
			$this->settings,
			$this->item_settings
		);

		$this->dashboard             = new Admin_Pages(
			$this->plugin,
			$this->html,
			$this->demo_import,
			$this->plugin_cpts
		);
		$this->acf_internal_features = new Acf_Internal_Features( $this->asset_resolver );

		$tools_settings     = new Tools_Settings( $this->group_creator );
		$debug_dump_creator = new Debug_Dump_Creator(
			$tools_settings,
			$this->logger,
			$this->layouts_settings_storage,
			$this->post_selections_settings_storage
		);
		$this->tools        = new Tools_Page(
			$tools_settings,
			$this->post_selections_settings_storage,
			$this->layouts_settings_storage,
			$this->plugin,
			$this->logger,
			$debug_dump_creator,
			$this->layout_cpt,
			$this->post_selection_cpt,
			$this->settings,
			$this->cache_flusher
		);

		$this->state_report  = new State_Report( $this->logger, $this->plugin, $this->settings );
		$this->usage_report  = new Usage_Report(
			$this->logger,
			$this->plugin,
			$this->settings,
			$this->state_report,
			array(
				$this->layouts_settings_storage,
				$this->post_selections_settings_storage,
			)
		);
		$this->settings_page = new Settings_Page(
			$this->logger,
			new Plugin_Settings( $this->group_creator ),
			$this->settings,
			$this->layouts_settings_storage,
			$this->post_selections_settings_storage,
			$this->group_creator->create( Git_Repository::class ),
			$this->state_report,
			$this->engines_storage
		);

		$this->admin_assets = new Admin_Assets(
			$this->asset_resolver,
			array(
				$this->layouts_loader->interactive_fields,
				$this->selections_loader->interactive_fields,
			)
		);

		$this->live_reloader = new Live_Reloader(
			$this->layouts_settings_storage,
			$this->post_selections_settings_storage,
			$this->layouts_loader->shortcode,
			$this->selections_loader->shortcode
		);

		$this->admin_bar = new Admin_Bar(
			$this->layouts_loader->shortcode,
			$this->selections_loader->shortcode,
			$this->live_reloader_component,
			$this->settings
		);

		$this->point_mounter = new Point_Mounter(
			array(
				new Point_Provider( $this->layouts_settings_storage, $this->layout_cpt ),
				new Point_Provider( $this->post_selections_settings_storage, $this->post_selection_cpt ),
			)
		);

		return parent::others();
	}

	/**
	 * @return Hookable[]
	 */
	protected function environment(): array {
		$this->plugin_environment = new Plugin_Environment(
			$this->templates_environment,
			$this->state_report,
			$this->usage_report,
			$this->settings,
			$this->plugin,
			$this->selections_loader->pre_built_tab,
			$this->file_systems,
			array( $this->layouts_settings_storage, $this->post_selections_settings_storage )
		);

		return parent::environment();
	}
}
