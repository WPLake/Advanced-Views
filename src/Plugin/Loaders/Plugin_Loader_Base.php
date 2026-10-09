<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Module;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Upgrade_Notice;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\Bootstrap\Version_Migrations_Module;
use Org\Wplake\Advanced_Views\Field_Provider\Data_Vendors;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\State_Report;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\Usage_Report;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Bar;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Pages;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Demo_Importer;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools_Page;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Repository\Repository_Factory;
use Org\Wplake\Advanced_Views\Plugin\Module_Loader;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Environment;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Page;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\Cache_Flusher;
use Org\Wplake\Advanced_Views\Plugin\Utils\Profiler;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap\Layout_Acf_Module;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Repeater_Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Bootstrap\Layouts_Module;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Bootstrap\Layout_Integrations_Module;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Bootstrap\Layout_Tabs_Module;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Bootstrap\Selection_Acf_Module;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Bootstrap\Post_Selections_Module;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Bootstrap\Selection_Integrations_Module;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Bootstrap\Selection_Tabs_Module;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Bootstrap\Template_Engine_Module;
use Org\Wplake\Advanced_Views\Assets\Bootstrap\Assets_Module;
use Org\Wplake\Advanced_Views\Bridge\Bootstrap\Bridge_Module;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;

abstract class Plugin_Loader_Base extends Module_Loader {
	public Plugin $plugin;
	public Plugin_Environment $plugin_environment;
	public Logger $logger;
	public Layout_Settings_Storage $layouts_settings_storage;

	public Data_Vendors $provider_cluster;
	public Live_Reloader_Component $live_reloader_component;
	/**
	 * @var File_System[]
	 */
	public array $file_systems = array();

	public Item_Settings $item_settings;
	public Settings_Storage $settings;
	public Creator $group_creator;
	public Admin_Pages $dashboard;
	public Demo_Importer $demo_import;
	public Usage_Report $usage_report;
	public State_Report $state_report;
	public Tools_Page $tools;
	public Settings_Page $settings_page;
	public Live_Reloader $live_reloader;
	public Admin_Bar $admin_bar;
	public Upgrade_Notice $upgrade_notice;
	public Cache_Flusher $cache_flusher;
	public Point_Mounter $point_mounter;
	public Git_Lab_Api $git_lab_api;

	public Selection_Settings_Storage $post_selections_settings_storage;

	/**
	 * @var Plugin_Cpt[]
	 */
	protected array $plugin_cpts = array();
	/**
	 * @var array<string, string> domain => relative_path
	 */
	protected array $lang_relative_paths = array();

	public function __construct() {
		$bootstrap_classes = static::get_bootstraps();
		$container         = Repository_Factory::build( $bootstrap_classes );

		parent::__construct( $container );

		$this->lang_relative_paths['acf-views'] = 'lang';
	}

	public function load(): void {
		$start_timestamp = microtime( true );

		$route_detector = $this->resolve( Route_Detector::class );

		$this->load_hookable( $this->load_modules( $route_detector ) );

		Profiler::plugin_loaded( $start_timestamp );
	}

	/**
	 * @return Actor[]
	 */
	protected function load_modules( Route_Detector $route_detector ): array {
		$this->translations( $route_detector );
		$primary = $this->primary();

		// layouts and selections instances are used by the next modules, so bootstraps go first.
		$modules_bootstrap = $this->resolve( Actor_Bootstrap::class );
		$bootstrap_classes = static::get_bootstraps();
		$modules_bootstrap->bootstrap( $bootstrap_classes );

		$integration = $this->integration( $route_detector );
		$others      = $this->others();
		$environment = $this->environment();

		return array_merge( $primary, $integration, $others, $environment );
	}

	protected function translations( Route_Detector $route_detector ): void {
		// on the whole admin area, as menu items need translations.
		if ( $route_detector->is_admin_route() ) {
			add_action(
				'after_setup_theme',
				function (): void {
					foreach ( $this->lang_relative_paths as $domain => $relative_path ) {
						$path = $this->plugin->get_relative_plugins_path( $relative_path );

						load_plugin_textdomain(
							$domain,
							false,
							$path
						);
					}
				},
				// make sure it's before the acf groups loading.
				8
			);
		}
	}

	/**
	 * @return Actor[]
	 */
	protected function primary(): array {
		// it's a hack, but there is no other way to pass data (constructor is always called automatically).
		Field_Settings::set_provider_cluster( $this->provider_cluster );

		return array_merge(
			array(
				$this->logger,
				$this->plugin,
				$this->provider_cluster,
				$this->live_reloader_component,
				$this->upgrade_notice,
				File_System_Loader::instance(),
			),
			$this->file_systems
		);
	}

	/**
	 * @return Actor[]
	 */
	protected function integration( Route_Detector $route_detector ): array {
		$save_actions   = $this->resolve( Layout_Save_Actions::class );
		$layout_factory = $this->resolve( Layout_Factory::class );
		$repeater_field = $this->group_creator->create( Repeater_Field_Settings::class );
		$shortcode      = $this->resolve( Layout_Shortcode::class );
		$layouts_cpt    = $this->resolve( Layouts_Cpt::class );

		// only now, when layouts() are called.
		$this->provider_cluster->make_integration_instances(
			$route_detector,
			$this->item_settings,
			$this->layouts_settings_storage,
			$save_actions,
			$layout_factory,
			$repeater_field,
			$shortcode,
			$this->settings,
			$layouts_cpt,
		);

		return array();
	}

	/**
	 * @return Actor[]
	 */
	protected function others(): array {
		return array(
			$this->dashboard,
			$this->demo_import,
			// only after late dependencies were set.
			$this->usage_report,
			$this->state_report,
			$this->tools,
			$this->settings_page,
			$this->live_reloader,
			$this->admin_bar,
			$this->point_mounter,
		);
	}

	/**
	 * @return Actor[]
	 */
	protected function environment(): array {
		$slug = $this->plugin->get_slug();

		register_activation_hook(
			$slug,
			array( $this->plugin_environment, 'prepare_environment' )
		);

		register_deactivation_hook(
			$slug,
			array( $this->plugin_environment, 'clean_environment' )
		);

		return array( $this->plugin_environment );
	}

	/**
	 * @param File_System[] $file_systems
	 */
	protected function add_file_systems( array $file_systems ): void {
		$this->file_systems = array_merge( $this->file_systems, $file_systems );
	}

	/**
	 * @return array<string, callable():boolean>
	 */
	protected function get_cache_cleaners(): array {
		/**
		 * @var array<string, callable():boolean> $cache_cleaners
		 */
		$cache_cleaners = array(
			// Redis - upgrades may have had direct DB changes.
			'wpdb' => 'wp_cache_flush',
		);

		// Opcache - upgrades may have had FS changes (e.g. theme template updates).
		if ( function_exists( 'opcache_reset' ) ) {
			$cache_cleaners['opcache'] = 'opcache_reset';
		}

		return $cache_cleaners;
	}

	/**
	 * @return class-string<Module_Base>[]
	 */
	protected static function get_bootstraps(): array {
		return array(
			Acf_Module::class,
			Template_Engine_Module::class,
			Assets_Module::class,
			Bridge_Module::class,
			// layouts.
			Layouts_Module::class,
			Layout_Acf_Module::class,
			Layout_Tabs_Module::class,
			Layout_Integrations_Module::class,
			// post_selections.
			Post_Selections_Module::class,
			Selection_Acf_Module::class,
			Selection_Tabs_Module::class,
			Selection_Integrations_Module::class,
			// fixme other domain bootstraps.
			Version_Migrations_Module::class,
		);
	}
}
