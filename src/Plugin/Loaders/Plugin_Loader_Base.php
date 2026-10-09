<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Bootstrap;
use Org\Wplake\Advanced_Views\Assets\Bootstrap\Assets_Bootstrap;
use Org\Wplake\Advanced_Views\Bridge\Bootstrap\Bridge_Bootstrap;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\Bootstrap\Version_Migrations_Bootstrap;
use Org\Wplake\Advanced_Views\Field_Provider\Bootstrap\Field_Provider_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Bootstrap\Plugin_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Bootstrap\Dashboard_Bootstrap;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Repository\Repository_Factory;
use Org\Wplake\Advanced_Views\Plugin\Module_Loader;
use Org\Wplake\Advanced_Views\Plugin\Plugin_File;
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
use Org\Wplake\Advanced_Views\Template\Template_Engine\Bootstrap\Template_Engine_Bootstrap;

abstract class Plugin_Loader_Base extends Module_Loader {
	public function __construct( string $plugin_file ) {
		$bootstrap_classes = static::get_bootstraps();
		$container         = Repository_Factory::build( $bootstrap_classes );

		parent::__construct( $container );

		$this->wire( Plugin_File::class, new Plugin_File( $plugin_file ) );
	}

	public function load(): void {
		$start_timestamp = microtime( true );

		$this->resolve( Actor_Bootstrap::class )->bootstrap( static::get_bootstraps() );

		Profiler::plugin_loaded( $start_timestamp );
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
			// shared post_type items (mounter, git).
			Post_Type_Bootstrap::class,
			// migrations.
			Version_Migrations_Bootstrap::class,
		);
	}
}
