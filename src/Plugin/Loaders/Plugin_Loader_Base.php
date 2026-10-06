<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

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
use Org\Wplake\Advanced_Views\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Repeater_Field_Settings;
use Org\Wplake\Advanced_Views\Assets\Admin_Assets;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Bridge\Advanced_Views;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Upgrade_Notice;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\Version_Migrations_Bootstrap;
use Org\Wplake\Advanced_Views\Field_Provider\Data_Vendors;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\State_Report;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\Usage_Report;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Bar;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Pages;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Demo_Importer;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools_Page;
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
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Templates_Environment;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;
use Org\Wplake\Advanced_Views\Vendors\DI\ContainerBuilder;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Loader;
use function Org\Wplake\Advanced_Views\Utils\flat_map;
use function Org\Wplake\Advanced_Views\Utils\resolve_instances;

abstract class Plugin_Loader_Base extends Module_Loader {
	public Container $container;
	public Plugin $plugin;
	public Asset_Resolver $asset_resolver;
	public Plugin_Environment $plugin_environment;
	public Logger $logger;
	public Layout_Settings_Storage $layouts_settings_storage;

	public Templates_Environment $templates_environment;
	public Data_Vendors $provider_cluster;
	public Front_Assets $front_assets;
	public Live_Reloader_Component $live_reloader_component;
	/**
	 * @var File_System[]
	 */
	public array $file_systems = array();

	public Acf_Dependency $acf_dependency;
	public Layout_Settings_Integration $layout_settings_integration;
	public Field_Settings_Integration $field_settings_integration;
	public Post_Selection_Settings_Integration $post_selection_settings_integration;
	public Item_Settings_Integration $item_settings_integration;
	public Meta_Field_Settings_Integration $meta_field_settings_integration;
	public Mount_Point_Settings_Integration $layout_mount_point_integration;
	public Mount_Point_Settings_Integration $post_selection_mount_point_integration;
	public Tax_Field_Settings_Integration $tax_field_settings_integration;
	public Tools_Settings_Integration $tools_settings_integration;
	public Custom_Acf_Field_Types $custom_acf_field_types;
	public Item_Settings $item_settings;
	public Settings_Storage $settings;
	public Creator $group_creator;
	public Admin_Pages $dashboard;
	public Demo_Importer $demo_import;
	public Acf_Internal_Features $acf_internal_features;
	public Usage_Report $usage_report;
	public State_Report $state_report;
	public Tools_Page $tools;
	public Admin_Assets $admin_assets;
	public Settings_Page $settings_page;
	public Live_Reloader $live_reloader;
	public Admin_Bar $admin_bar;
	public Upgrade_Notice $upgrade_notice;
	public Cache_Flusher $cache_flusher;
	public Point_Mounter $point_mounter;
	public Git_Lab_Api $git_lab_api;

	public Engines_Storage $engines_storage;
	public Selection_Settings_Storage $post_selections_settings_storage;
	public Post_Selections_Loader_Base $selections_loader;

	/**
	 * @var Plugin_Cpt[]
	 */
	protected array $plugin_cpts = array();
	/**
	 * @var array<string, string> domain => relative_path
	 */
	protected array $lang_relative_paths = array();

	public function __construct() {
		parent::__construct();

		$this->lang_relative_paths['acf-views'] = 'lang';

		$this->container = self::create_container();
	}

	public function load(): void {
		$start_timestamp = microtime( true );

		$route_detector = $this->container->get( Route_Detector::class );

		$this->load_hookable( $this->load_modules( $route_detector ) );

		Profiler::plugin_loaded( $start_timestamp );
	}

	/**
	 * @return Hookable[]
	 */
	protected function load_modules( Route_Detector $route_detector ): array {
		$this->translations( $route_detector );
		$primary = $this->primary();
		$this->acf_groups( $route_detector );
		// layouts instances are used by the next modules, so bootstraps go first.
		$bootstraps     = resolve_instances( $this->get_bootstraps(), $this->container );
		$modules        = $this->load_bootstraps( $bootstraps, $route_detector );
		$post_selection = $this->post_selections();
		$integration    = $this->integration( $route_detector );
		$others         = $this->others();
		$this->bridge();
		$environment = $this->environment();

		$this->add_plugin_extensions( $bootstraps );

		return array_merge( $primary, $modules, $post_selection, $integration, $others, $environment );
	}

	protected function translations( Route_Detector $route_detector ): void {
		// on the whole admin area, as menu items need translations.
		if ( ! $route_detector->is_admin_route() ) {
			return;
		}

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
			// make sure it's before acf_groups.
			8
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function primary(): array {
		// it's a hack, but there is no other way to pass data (constructor is always called automatically).
		Field_Settings::set_provider_cluster( $this->provider_cluster );

		return array_merge(
			array(
				$this->logger,
				$this->plugin,
				$this->templates_environment,
				$this->front_assets,
				$this->provider_cluster,
				$this->live_reloader_component,
				$this->upgrade_notice,
				File_System_Loader::instance(),
			),
			$this->file_systems
		);
	}

	protected function acf_groups( Route_Detector $route_detector ): void {
		if ( ! wp_doing_ajax() &&
			false === $route_detector->is_cpt_admin_route( $this->container->get( Layouts_Cpt::class )->cpt_name() ) &&
			false === $route_detector->is_cpt_admin_route( $this->container->get( Selections_Cpt::class )->cpt_name() ) ) {
			return;
		}

		add_action(
			'acf/init',
			function (): void {
				$loader = new Loader();

				$loader->signUpGroups(
					'Org\Wplake\Advanced_Views\Acf\Groups',
					$this->plugin->get_plugin_path( 'src/Acf/Groups' )
				);
			},
			// make sure it's after translations.
			9
		);
	}

	/**
	 * @param Module_Bootstrap[] $bootstraps
	 *
	 * @return Hookable[]
	 */
	protected function load_bootstraps( array $bootstraps, Route_Detector $route_detector ): array {
		return flat_map(
			$bootstraps,
			fn( Module_Bootstrap $bootstrap ): array => $bootstrap->get_hookables( $route_detector )
		);
	}

	/**
	 * @param Module_Bootstrap[] $bootstraps
	 */
	protected function add_plugin_extensions( array $bootstraps ): void {
		add_action(
			'plugins_loaded',
			function () use ( $bootstraps ): void {
				foreach ( $bootstraps as $bootstrap ) {
					foreach ( $bootstrap->get_plugin_extensions() as $loaded_action => $make_hookables ) {
						if ( did_action( $loaded_action ) > 0 ) {
							$this->load_hookable( $make_hookables() );
						}
					}
				}
			},
			11
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function post_selections(): array {
		return $this->selections_loader->hookable();
	}

	/**
	 * @return Hookable[]
	 */
	protected function integration( Route_Detector $route_detector ): array {
		// only now, when layouts() are called.
		$this->provider_cluster->make_integration_instances(
			$route_detector,
			$this->item_settings,
			$this->layouts_settings_storage,
			$this->container->get( Layout_Save_Actions::class ),
			$this->container->get( Layout_Factory::class ),
			$this->group_creator->create( Repeater_Field_Settings::class ),
			$this->container->get( Layout_Shortcode::class ),
			$this->settings,
			$this->container->get( Layouts_Cpt::class ),
		);

		return array(
			$this->acf_dependency,
			$this->layout_settings_integration,
			$this->field_settings_integration,
			$this->post_selection_settings_integration,
			$this->item_settings_integration,
			$this->meta_field_settings_integration,
			$this->layout_mount_point_integration,
			$this->post_selection_mount_point_integration,
			$this->tax_field_settings_integration,
			$this->tools_settings_integration,
			$this->custom_acf_field_types,
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function others(): array {
		return array(
			$this->dashboard,
			$this->demo_import,
			$this->acf_internal_features,
			// only after late dependencies were set.
			$this->usage_report,
			$this->state_report,
			$this->tools,
			$this->admin_assets,
			$this->settings_page,
			$this->live_reloader,
			$this->admin_bar,
			$this->point_mounter,
		);
	}

	protected function bridge(): void {
		Advanced_Views::$layout_renderer         = $this->container->get( Layout_Shortcode::class );
		Advanced_Views::$post_selection_renderer = $this->selections_loader->shortcode;
	}

	/**
	 * @return Hookable[]
	 */
	protected function environment(): array {
		register_activation_hook(
			$this->plugin->get_slug(),
			array( $this->plugin_environment, 'prepare_environment' )
		);

		register_deactivation_hook(
			$this->plugin->get_slug(),
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

	protected static function uploads_folder(): string {
		return wp_upload_dir()['basedir'] . '/acf-views';
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
	 * @return array<array-key, class-string<Module_Bootstrap>|Module_Bootstrap>
	 */
	protected function get_bootstraps(): array {
		return array(
			Layouts_Bootstrap::class,
			Version_Migrations_Bootstrap::class,
		);
	}

	protected static function create_container(): Container {
		$builder = new ContainerBuilder();

		$builder->useAutowiring( true );
		$builder->useAnnotations( false );

		return $builder->build();
	}
}
