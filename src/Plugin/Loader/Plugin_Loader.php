<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loader;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Bootstrap;
use Org\Wplake\Advanced_Views\Assets\Bootstrap\Assets_Bootstrap;
use Org\Wplake\Advanced_Views\Bridge\Bootstrap\Bridge_Bootstrap;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\Bootstrap\Version_Migrations_Bootstrap;
use Org\Wplake\Advanced_Views\Field_Provider\Bootstrap\Field_Provider_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Bootstrap\Plugin_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Bootstrap\Dashboard_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Environment\Plugin_Tester;
use Org\Wplake\Advanced_Views\Plugin\Loader\Repository\Instance_Repository;
use Org\Wplake\Advanced_Views\Plugin\Loader\Repository\Repository_Factory;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Meta;
use Org\Wplake\Advanced_Views\Plugin\Utils\Profiler;
use Org\Wplake\Advanced_Views\Post_Type\Bootstrap\Post_Type_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap\Layout_Acf_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Bootstrap\Layouts_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Bootstrap\Layout_Integrations_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Bootstrap\Layout_Tabs_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Bootstrap\Selection_Acf_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Bootstrap\Post_Selections_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Bootstrap\Selection_Integrations_Bootstrap;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Bootstrap\Selection_Tabs_Bootstrap;
use Org\Wplake\Advanced_Views\Template\Html\Bootstrap\Html_Bootstrap;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Bootstrap\Template_Engine_Bootstrap;

class Plugin_Loader {
	public static function load( string $plugin_file ): void {
		$start_timestamp = microtime( true );

		if ( ! Plugin_Tester::is_test_bootstrap() ) {
			$plugin_meta = new Plugin_Meta( $plugin_file );
			static::bootstrap_actors( $plugin_meta );
		}

		Profiler::plugin_loaded( $start_timestamp );
	}

	public static function bootstrap_actors( Plugin_Meta $meta ): Instance_Repository {
		$bootstraps = static::get_bootstraps();

		$repository = Repository_Factory::build( $bootstraps );
		$repository->wire( Plugin_Meta::class, $meta );
		$actor_bootstrap = $repository->resolve( Actor_Bootstrap::class );

		$actor_bootstrap->bootstrap( $bootstraps );

		return $repository;
	}

	/**
	 * @return class-string<Bootstrap_Base>[]
	 */
	protected static function get_bootstraps(): array {
		return array(
			Acf_Bootstrap::class,
			Template_Engine_Bootstrap::class,
			Html_Bootstrap::class,
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
			// shared post_type items (mounter, git).
			Post_Type_Bootstrap::class,
			// migrations.
			Version_Migrations_Bootstrap::class,
		);
	}
}
