<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders\Lite;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Dependency;
use Org\Wplake\Advanced_Views\Acf\Acf_Internal_Features;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Custom_Acf_Field_Types;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Tools_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Groups\Git_Repository;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Theme_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Plugin_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Tools_Settings;
use Org\Wplake\Advanced_Views\Assets\Admin_Assets;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Upgrade_Notice;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Data_Vendors;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\State_Report;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\Usage_Report;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
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
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Api_Interface;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Post_Selection_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Templates_Environment;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Interfaces\CreatorInterface;

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
	 * @return Actor[]
	 */
	protected function primary(): array {
		$layout_cpt    = $this->resolve( Layouts_Cpt::class );
		$selection_cpt = $this->resolve( Selections_Cpt::class );

		$this->plugin_cpts = array(
			$layout_cpt,
			$selection_cpt,
		);

		$this->options  = $this->resolve( Options_Storage::class );
		$this->settings = $this->resolve( Settings_Storage::class );
		$this->wire( Cpt_Theme_Settings::class, $this->settings );

		$uploads_folder = Plugin::uploads_folder();
		$this->logger   = new Logger( $uploads_folder, $this->settings );
		$this->wire( Logger::class, $this->logger );

		$this->group_creator = new Creator();
		$this->wire( Creator::class, $this->group_creator );
		$this->wire( CreatorInterface::class, $this->group_creator );

		$this->layout_settings         = $this->group_creator->create( Layout_Settings::class );
		$this->post_selection_settings = $this->group_creator->create( Post_Selection_Settings::class );
		$this->wire( Layout_Settings::class, $this->layout_settings );
		$this->wire( Post_Selection_Settings::class, $this->post_selection_settings );

		$this->html            = $this->resolve( Html_Printer::class );

		$post_selections_file_system            = new File_System(
			$this->logger,
			$selection_cpt->folder_name()
		);
		$this->post_selections_settings_storage = new Selection_Settings_Storage(
			$this->logger,
			$post_selections_file_system,
			$this->resolve( Post_Selection_Fs_Fields::class ),
			new Db_Management( $this->logger, $post_selections_file_system, $selection_cpt ),
			$this->post_selection_settings
		);
		$this->wire( Selection_Settings_Storage::class, $this->post_selections_settings_storage );

		$layouts_file_system            = new File_System( $this->logger, $layout_cpt->folder_name() );
		$this->layouts_settings_storage = new Layout_Settings_Storage(
			$this->logger,
			$layouts_file_system,
			$this->resolve( Layout_Fs_Fields::class ),
			new Db_Management( $this->logger, $layouts_file_system, $layout_cpt ),
			$this->layout_settings
		);
		$this->wire( Layout_Settings_Storage::class, $this->layouts_settings_storage );

		$this->plugin = new Plugin( $this->plugin_file, $this->options, $this->settings );
		$this->wire( Plugin::class, $this->plugin );
		$this->asset_resolver = new Asset_Resolver( $this->plugin_file, $this->plugin->get_version() );
		$this->wire( Asset_Resolver::class, $this->asset_resolver );

		$this->item_settings = $this->group_creator->create( Item_Settings::class );
		$this->wire( Item_Settings::class, $this->item_settings );

		$this->provider_cluster = $this->resolve( Data_Vendors::class );
		$this->wire( Field_Provider_Cluster::class, $this->provider_cluster );

		$this->live_reloader_component = $this->resolve( Live_Reloader_Component::class );
		$this->front_assets            = new Front_Assets(
			$this->asset_resolver,
			$layouts_file_system,
			$this->provider_cluster,
			$this->live_reloader_component
		);
		$this->wire( Front_Assets::class, $this->front_assets );
		$this->git_lab_api = new Git_Lab_Api(
			$this->logger,
			$this->options,
			$layout_cpt,
			$selection_cpt
		);
		$this->wire( Git_Lab_Api::class, $this->git_lab_api );
		$this->wire( Git_Api_Interface::class, $this->git_lab_api );
		$this->upgrade_notice = $this->resolve( Upgrade_Notice::class );
		$this->cache_flusher  = new Cache_Flusher( $this->logger, $this->get_cache_cleaners() );
		$this->wire( Cache_Flusher::class, $this->cache_flusher );

		$this->add_file_systems(
			array(
				$layouts_file_system,
				$post_selections_file_system,
			)
		);

		return parent::primary();
	}

	/**
	 * @return Actor[]
	 */
	protected function integration( Route_Detector $route_detector ): array {
		$this->acf_dependency             = $this->resolve( Acf_Dependency::class );
		$this->tools_settings_integration = $this->resolve( Tools_Settings_Integration::class );
		$this->custom_acf_field_types     = $this->resolve( Custom_Acf_Field_Types::class );

		return parent::integration( $route_detector );
	}

	/**
	 * @return Actor[]
	 */
	protected function others(): array {
		$layout_cpt    = $this->resolve( Layouts_Cpt::class );
		$selection_cpt = $this->resolve( Selections_Cpt::class );

		$this->demo_import = $this->resolve( Demo_Importer::class );

		$this->dashboard             = new Admin_Pages(
			$this->plugin,
			$this->html,
			$this->demo_import,
			$this->plugin_cpts
		);
		$this->acf_internal_features = $this->resolve( Acf_Internal_Features::class );

		$this->tools = new Tools_Page(
			$this->resolve( Tools_Settings::class ),
			$this->post_selections_settings_storage,
			$this->layouts_settings_storage,
			$this->plugin,
			$this->logger,
			$this->resolve( Debug_Dump_Creator::class ),
			$layout_cpt,
			$selection_cpt,
			$this->settings,
			$this->cache_flusher
		);

		$this->state_report  = $this->resolve( State_Report::class );
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
			$this->resolve( Plugin_Settings::class ),
			$this->settings,
			$this->layouts_settings_storage,
			$this->post_selections_settings_storage,
			$this->group_creator->create( Git_Repository::class ),
			$this->state_report,
			$this->resolve( Engines_Storage::class )
		);

		$this->admin_assets = new Admin_Assets(
			$this->asset_resolver,
			array(
				$this->resolve( Layout_Interactive_Fields::class ),
				$this->resolve( Selection_Interactive_Fields::class ),
			)
		);

		$this->live_reloader = $this->resolve( Live_Reloader::class );
		$this->admin_bar     = $this->resolve( Admin_Bar::class );

		$this->point_mounter = new Point_Mounter(
			array(
				new Point_Provider( $this->layouts_settings_storage, $layout_cpt ),
				new Point_Provider( $this->post_selections_settings_storage, $selection_cpt ),
			)
		);

		return parent::others();
	}

	/**
	 * @return Actor[]
	 */
	protected function environment(): array {
		$this->plugin_environment = new Plugin_Environment(
			$this->resolve( Templates_Environment::class ),
			$this->state_report,
			$this->usage_report,
			$this->settings,
			$this->plugin,
			$this->resolve( Post_Selections_Pre_Built_Tab::class ),
			$this->file_systems,
			array( $this->layouts_settings_storage, $this->post_selections_settings_storage )
		);

		return parent::environment();
	}
}
