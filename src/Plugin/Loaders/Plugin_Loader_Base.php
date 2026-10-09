<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Bootstrap;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\Bootstrap\Version_Migrations_Bootstrap;
use Org\Wplake\Advanced_Views\Field_Provider\Bootstrap\Field_Provider_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Bootstrap\Dashboard_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Repository\Repository_Factory;
use Org\Wplake\Advanced_Views\Plugin\Module_Loader;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Utils\Cache_Flusher;
use Org\Wplake\Advanced_Views\Plugin\Utils\Profiler;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap\Layout_Acf_Bootstrap;
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

abstract class Plugin_Loader_Base extends Module_Loader {
	public Plugin $plugin;
	public Layout_Settings_Storage $layouts_settings_storage;

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
		return array(
			$this->plugin,
			File_System_Loader::instance(),
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
			$this->point_mounter,
		);
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
