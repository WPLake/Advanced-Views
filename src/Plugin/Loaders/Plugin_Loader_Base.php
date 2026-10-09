<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Bootstrap;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Upgrade_Notice;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\Bootstrap\Version_Migrations_Bootstrap;
use Org\Wplake\Advanced_Views\Field_Provider\Bootstrap\Field_Provider_Bootstrap;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\State_Report;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\Usage_Report;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Bootstrap\Dashboard_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Repository\Repository_Factory;
use Org\Wplake\Advanced_Views\Plugin\Module_Loader;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Page;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\Cache_Flusher;
use Org\Wplake\Advanced_Views\Plugin\Utils\Profiler;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap\Layout_Acf_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Bootstrap\Layouts_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Bootstrap\Layout_Integrations_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Bootstrap\Layout_Tabs_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Bootstrap\Selection_Acf_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Bootstrap\Post_Selections_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Bootstrap\Selection_Integrations_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Bootstrap\Selection_Tabs_Bootstrap;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Bootstrap\Template_Engine_Bootstrap;
use Org\Wplake\Advanced_Views\Assets\Bootstrap\Assets_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Bootstrap\Plugin_Bootstrap;
use Org\Wplake\Advanced_Views\Bridge\Bootstrap\Bridge_Bootstrap;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;

abstract class Plugin_Loader_Base extends Module_Loader {
	public Plugin $plugin;
	public Logger $logger;
	public Layout_Settings_Storage $layouts_settings_storage;

	/**
	 * @var File_System[]
	 */
	public array $file_systems = array();

	public Settings_Storage $settings;
	public Creator $group_creator;
	public Usage_Report $usage_report;
	public State_Report $state_report;
	public Settings_Page $settings_page;
	public Upgrade_Notice $upgrade_notice;
	public Cache_Flusher $cache_flusher;
	public Point_Mounter $point_mounter;
	public Git_Lab_Api $git_lab_api;

	public Selection_Settings_Storage $post_selections_settings_storage;

	public function __construct() {
		$bootstrap_classes = static::get_bootstraps();
		$container         = Repository_Factory::build( $bootstrap_classes );

		parent::__construct( $container );
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
		$primary = $this->primary();

		// layouts and selections instances are used by the next modules, so bootstraps go first.
		$modules_bootstrap = $this->resolve( Actor_Bootstrap::class );
		$bootstrap_classes = static::get_bootstraps();
		$modules_bootstrap->bootstrap( $bootstrap_classes );

		$integration = $this->integration( $route_detector );
		$others      = $this->others();

		return array_merge( $primary, $integration, $others );
	}

	/**
	 * @return Actor[]
	 */
	protected function primary(): array {
		// it's a hack, but there is no other way to pass data (constructor is always called automatically).
		Field_Settings::set_provider_cluster( $this->resolve( Field_Provider_Cluster::class ) );

		return array_merge(
			array(
				$this->logger,
				$this->plugin,
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
		return array();
	}

	/**
	 * @return Actor[]
	 */
	protected function others(): array {
		return array(
			// only after late dependencies were set.
			$this->usage_report,
			$this->state_report,
			$this->settings_page,
			$this->point_mounter,
		);
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
	 * @return class-string<Bootstrap_Base>[]
	 */
	protected static function get_bootstraps(): array {
		return array(
			Acf_Bootstrap::class,
			Template_Engine_Bootstrap::class,
			Field_Provider_Bootstrap::class,
			Plugin_Bootstrap::class,
			Assets_Bootstrap::class,
			Bridge_Bootstrap::class,
			Dashboard_Bootstrap::class,
			// layouts.
			Layouts_Bootstrap::class,
			Layout_Acf_Bootstrap::class,
			Layout_Tabs_Bootstrap::class,
			Layout_Integrations_Bootstrap::class,
			// post_selections.
			Post_Selections_Bootstrap::class,
			Selection_Acf_Bootstrap::class,
			Selection_Tabs_Bootstrap::class,
			Selection_Integrations_Bootstrap::class,
			// fixme other domain bootstraps.
			Version_Migrations_Bootstrap::class,
		);
	}
}
